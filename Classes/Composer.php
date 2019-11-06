<?php

namespace OnePilot\ClientBundle\Classes;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use OnePilot\ClientBundle\Contracts\PackageDetector;
use OnePilot\ClientBundle\Traits\Instantiable;

class Composer
{
    use Instantiable;

    /** @var array */
    protected static $installedPackages;

    /** @var array */
    protected static $packagesConstraints;

    /** @var array */
    protected $packagist = [];

    /**
     * Composer constructor.
     *
     * @param PackageDetector $detector
     */
    public function __construct(PackageDetector $detector)
    {
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
        $chunks = array_chunk(self::$installedPackages, 50, true);

        foreach ($chunks as $chunk) {
            $promises = [];
            $client = new Client(['allow_redirects' => false]);

            foreach ($chunk as $package) {
                if (empty($package->version) || empty($package->name)) {
                    continue;
                }

                $promises[$package->name] = $client
                    ->getAsync($this->getPackagistDetailUrl($package->name))
                    ->then(function (Response $response) use (&$packages, $package) {
                        if ($response->getStatusCode() === 200) {
                            $this->storePackagistVersions($package->name, $response->getBody());
                        }

                        $packages[] = $this->generatePackageData($package);
                    }, function ($e) use (&$packages, $package) {
                        // if fail re-try with file_get_contents (@see self::getVersionsFromPackagist)
                        $packages[] = $this->generatePackageData($package);
                    });
            }

            \GuzzleHttp\Promise\settle($promises)->wait();
        }

        return $packages;
    }

    /**
     * Get new compatible (follow constraints) & available versions number of a package
     * Only version not equals to current version are returned
     *
     * @param string $packageName    The name of the package as registered on packagist, e.g. 'laravel/framework'
     * @param string $currentVersion If provided will ignore this version (if last one is $currentVersion will return null)
     *
     * @return array ['compatible' => $version, 'available' => $version]
     */
    public function getNewCompatibleAndAvailableVersionsNumber($packageName, $currentVersion = null)
    {
        if (empty($versions = $this->getLatestCompatibleAndAvailableVersions($packageName))) {
            return null;
        }

        foreach ($versions as $key => $value) {
            $version = $this->removePrefix($value->version ?? null);
            $versions[$key] = $version == $currentVersion ? null : $version;
        }

        return $versions;
    }

    /**
     * @param $package
     *
     * @return array
     */
    private function generatePackageData($package)
    {
        $currentVersion = $this->removePrefix($package->version);
        $latestVersion = $this->getNewCompatibleAndAvailableVersionsNumber($package->name, $currentVersion);

        return [
            'name' => Helpers::strAfter($package->name, '/'),
            'code' => $package->name,
            'type' => 'package',
            'active' => 1,
            'version' => $currentVersion,
            'new_version' => $latestVersion['compatible'] ?? null,
            'last_available_version' => $latestVersion['available'] ?? null,
        ];
    }

    /**
     * Get latest compatible (follow constraints) & available versions objects of a package
     *
     * @param string $packageName , the name of the package as registered on packagist, e.g. 'laravel/framework'
     *
     * @return array ['compatible' => (object) $version, 'available' => (object) $version]
     */
    private function getLatestCompatibleAndAvailableVersions($packageName)
    {
        if (empty($versions = $this->getVersionsFromPackagist($packageName))) {
            return null;
        }

        $lastCompatibleVersion = null;
        $lastAvailableVersion = null;

        $packageConstraints = self::$packagesConstraints[$packageName] ?? null;

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
                $lastCompatibleVersion = $lastAvailableVersion;
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
        if (empty($version) || !Helpers::strStartsWith($version, $prefix)) {
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
        return 'https://packagist.org/packages/' . $packageName . '.json';
    }

    private function storePackagistVersions(string $package, string $response)
    {
        $packagistInfo = json_decode($response);

        $this->packagist[$package] = $packagistInfo->package->versions;
    }

    private function getVersionsFromPackagist(string $package)
    {
        if (empty($versions = $this->packagist[$package] ?? null)) {
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
}
