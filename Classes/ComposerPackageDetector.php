<?php

namespace OnePilot\ClientBundle\Classes;

use Illuminate\Support\Collection;
use OnePilot\ClientBundle\Contracts\PackageDetector;

class ComposerPackageDetector implements PackageDetector
{
    /** @var string  */
    private $projectRoot;

    /**
     * ComposerPackageDetector constructor.
     *
     * @param string $projectRoot
     */
    public function __construct(string $projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }


    public function getPackages(): Collection
    {
        $installedJsonFile = $this->projectRoot . '/vendor/composer/installed.json';
        $installedPackages = json_decode(file_get_contents($installedJsonFile));

        return collect($installedPackages);
    }

    public function getPackagesConstraints(): Collection
    {
        $composers = $this->getPackages()
            ->push($this->appComposerData())
            ->filter()
            ->map(function ($package) {
                return $package->require ?? null;
            });

        $constraints = [];

        foreach ($composers as $packages) {
            foreach ($packages as $package => $constraint) {
                if (strpos($package, '/') === false) {
                    continue;
                }

                if (!isset($constraints[$package])) {
                    $constraints[$package] = [];
                }

                $constraints[$package][] = $constraint;
            }
        }

        return collect($constraints);
    }

    private function appComposerData()
    {
        if (!file_exists($this->projectRoot . '/composer.json')) {
            return null;
        }

        $content = file_get_contents($this->projectRoot . '/composer.json');

        return json_decode($content) ?? null;
    }
}
