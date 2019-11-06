<?php

namespace OnePilot\ClientBundle\Classes;

abstract class PackageDetectorAbstract
{
    public function getPackagesConstraints(): array
    {
        $packages = $this->getPackages();

        if (!empty($projectComposerContent = $this->projectComposerContent())) {
            $packages[] = $projectComposerContent;
        }

        $constraints = [];

        foreach ($packages as $package) {
            if (empty($package) || empty($package->require)) {
                continue;
            }

            foreach ($package->require as $requiredPackage => $constraint) {
                if (strpos($requiredPackage, '/') === false) {
                    continue;
                }

                if (!isset($constraints[$requiredPackage])) {
                    $constraints[$requiredPackage] = [];
                }

                $constraints[$requiredPackage][] = $constraint;
            }
        }

        return $constraints;
    }

    protected function projectComposerContent()
    {
        return null;
    }
}
