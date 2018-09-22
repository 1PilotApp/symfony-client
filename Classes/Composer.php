<?php

namespace OnePilot\ClientBundle\Classes;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Symfony\Component\Cache\Simple\FilesystemCache;
use OnePilot\ClientBundle\Contracts\PackageDetector;
use OnePilot\ClientBundle\Traits\Instantiable;
use Psr\SimpleCache\InvalidArgumentException;

class Composer
{
    use Instantiable;

    /** @var array */
    protected static $installedPackages;

    /** @var \Illuminate\Support\Collection */
    protected static $packagesConstraints;

    /** @var array */
    protected $packagist = [];

    /** @var FilesystemCache */
    protected $cache;

    /**
     * Composer constructor.
     *
     * @param PackageDetector $detector
     */
    public function __construct(PackageDetector $detector)
    {
        $this->cache = new FilesystemCache();

        self::$installedPackages = $detector->getPackages();

        self::$packagesConstraints = $detector->getPackagesConstraints();
    }

    /**
     * Get information for composer installed packages (currently installed version and latest stable version)
     *
     * @return array
     */
    public function getPackagesData()
    {
        $packages = [];

        collect(self::$installedPackages)
            ->chunk(50)
            ->each(function (Collection $chunk) use (&$packages) {
                $promises = [];
                $client = new Client();

                $chunk
                    ->filter(function ($package) {
                        return !empty($package->version) && !empty($package->name);
                    })
                    ->each(function ($package) use (&$packages, &$promises, $client) {

                        if ($this->isCached($package)) {
                            $packages[] = $this->generatePackageData($package);

                            return;
                        }

                        $promises[$package->name] = $client
                            ->getAsync($this->getPackagistDetailUrl($package->name))
                            ->then(function (Response $response) use (&$packages, $package) {
                                $this->storePackagistVersions($package->name, $response->getBody());

                                $packages[] = $this->generatePackageData($package);
                            });
                    });

                \GuzzleHttp\Promise\settle($promises)->wait();
            });

        return $packages;
    }

    /**
     * Get latest (stable) version number of composer package
     *
     * @param string $packageName    The name of the package as registered on packagist, e.g. 'laravel/framework'
     * @param string $currentVersion If provided will ignore this version (if last one is $currentVersion will return null)
     *
     * @return array ['compatible' => $version, 'available' => $version]
     */
    public function getLatestPackageVersion($packageName, $currentVersion = null)
    {
        $cacheKey = $this->getCacheKey($packageName, $currentVersion);
        if ($this->cache->has($cacheKey)) {
            try {
                return $this->cache->get($cacheKey);
            } catch (InvalidArgumentException $ex) {
                // @todo log error
            }
        }

        $packages = $this->getLatestPackage($packageName);
        $collection = collect($packages)->map(function ($package) use ($currentVersion) {
            $version = $this->removePrefix(optional($package)->version);

            return $version == $currentVersion ? null : $version;
        });

        try {
            $this->cache->set($cacheKey, $collection, 10);
        } catch (InvalidArgumentException $ex) {
            // @todo log error
        }

        return $collection;
    }

    /**
     * @param $package
     *
     * @return array
     */
    private function generatePackageData($package)
    {
        $currentVersion = $this->removePrefix($package->version);
        $latestVersion = $this->getLatestPackageVersion($package->name, $currentVersion);

        return [
            'name' => str_after($package->name, '/'),
            'code' => $package->name,
            'type' => 'package',
            'active' => 1,
            'version' => $currentVersion,
            'new_version' => $latestVersion['compatible'],
            'last_available_version' => $latestVersion['available'],
        ];
    }

    /**
     * Get latest (stable) package from packagist
     *
     * @param string $packageName , the name of the package as registered on packagist, e.g. 'laravel/framework'
     *
     * @return array ['compatible' => (object) $version, 'available' => (object) $version]
     */
    private function getLatestPackage($packageName)
    {
        if (empty($versions = $this->getVersionsFromPackagist($packageName))) {
            return null;
        }

        $lastCompatibleVersion = null;
        $lastAvailableVersion = null;

        $packageConstraints = self::$packagesConstraints->get($packageName);

        foreach ($versions as $versionData) {
            $versionNumber = $versionData->version;
            $normalizeVersionNumber = $versionData->version_normalized;
            $stability = VersionParser::normalizeStability(VersionParser::parseStability($versionNumber));

            // only use stable version numbers
            if ($stability !== 'stable') {
                continue;
            }

            if (version_compare($normalizeVersionNumber, $lastAvailableVersion->version_normalized ?? '', '>=')) {
                $lastAvailableVersion = $versionData;
            }

            if (empty($packageConstraints)) {
                continue;
            }

            // only use version that follow constraint
            if (
                version_compare($normalizeVersionNumber, $lastCompatibleVersion->version_normalized ?? '', '>=')
                && $this->checkConstraints($normalizeVersionNumber, $packageConstraints)
            ) {
                $lastCompatibleVersion = $versionData;
            }
        }

        if ($lastCompatibleVersion === $lastAvailableVersion) {
            $lastAvailableVersion = null;
        }

        return [
            'compatible' => $lastCompatibleVersion,
            'available' => $lastAvailableVersion,
        ];
    }

    /**
     * @param string $version
     *
     * @param string $prefix
     *
     * @return string
     */
    private function removePrefix($version, $prefix = 'v')
    {
        if (empty($version) || !starts_with($version, $prefix)) {
            return $version;
        }

        return substr($version, strlen($prefix));
    }

    private function checkConstraints($version, $constraints)
    {
        foreach ($constraints as $constraint) {
            if (Semver::satisfies($version, $constraint) !== true) {
                return false;
            }
        }

        return true;
    }

    private function getPackagistDetailUrl(string $packageName): string
    {
        return 'https://packagist.org/packages/'.$packageName.'.json';
    }

    private function storePackagistVersions(string $package, string $response)
    {
        $packagistInfo = json_decode($response);

        $this->packagist[$package] = $packagistInfo->package->versions;
    }

    private function getVersionsFromPackagist(string $package)
    {
        if (empty($versions = array_get($this->packagist, $package))) {
            try {
                $packagistInfo = json_decode(file_get_contents($this->getPackagistDetailUrl($package)));
                $versions = $packagistInfo->package->versions;
            } catch (\Exception $e) {
                return null;
            }
        }

        unset($this->packagist[$package]);

        if (!is_object($versions)) {
            return null;
        }

        return $versions;
    }

    /**
     * @param string $packageName
     * @param string $currentVersion
     *
     * @return string
     */
    private function getCacheKey($packageName, $currentVersion)
    {
        return 'onepilot-getLatestPackageVersion-'.md5($packageName.$currentVersion);
    }

    /**
     * @param $package
     *
     * @return bool
     */
    private function isCached($package)
    {
        return $this->cache->has($this->getCacheKey($package->name, $this->removePrefix($package->version)));
    }
}
