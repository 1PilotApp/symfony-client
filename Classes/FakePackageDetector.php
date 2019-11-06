<?php

namespace OnePilot\ClientBundle\Classes;

use OnePilot\ClientBundle\Contracts\PackageDetector;

class FakePackageDetector extends PackageDetectorAbstract implements PackageDetector
{
    /** @var array $packages */
    private static $packages;

    /** @var array $packagesConstraints */
    private static $packagesConstraints;

    public function getPackages(): array
    {
        return self::$packages;
    }

    public function getPackagesConstraints(): array
    {
        return self::$packagesConstraints ?: parent::getPackagesConstraints();
    }

    public static function setPackages(array $array)
    {
        self::$packages = $array;
    }

    public static function setPackagesFromArray(array $array)
    {
        self::setPackages(json_decode(json_encode($array)));
    }

    public static function setPackagesFromPath(string $path)
    {
        self::setPackages(json_decode(file_get_contents($path)));
    }

    public static function setPackagesConstraints(array $array)
    {
        self::$packagesConstraints = $array;
    }
}
