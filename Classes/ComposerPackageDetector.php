<?php

namespace OnePilot\ClientBundle\Classes;

use OnePilot\ClientBundle\Contracts\PackageDetector;

class ComposerPackageDetector extends PackageDetectorAbstract implements PackageDetector
{
    /** @var string */
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

    public function getPackages(): array
    {
        $installedJsonFile = $this->projectRoot . '/vendor/composer/installed.json';
        $installedPackages = json_decode(file_get_contents($installedJsonFile));

        if (!empty($installedPackages->packages)) {
            return $installedPackages->packages; // composer v2
        }

        return $installedPackages;
    }

    protected function projectComposerContent()
    {
        if (!file_exists($this->projectRoot . '/composer.json')) {
            return null;
        }

        $content = file_get_contents($this->projectRoot . '/composer.json');

        return json_decode($content) ?? null;
    }
}
