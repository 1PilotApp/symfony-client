<?php

namespace OnePilot\ClientBundle\Contracts;

interface PackageDetector
{
    public function getPackages(): array;

    public function getPackagesConstraints(): array;
}
