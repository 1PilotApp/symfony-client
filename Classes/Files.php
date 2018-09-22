<?php

namespace OnePilot\ClientBundle\Classes;

use OnePilot\ClientBundle\Traits\Instantiable;

class Files
{
    use Instantiable;

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

    /**
     * Get data for some important system files
     *
     * @return array
     */
    public function getFilesProperties()
    {
        $filesProperties = [];

        $files = [
            '/.env',
            '/web/app.php',
            '/web/app_dev.php',
            '/web/.htaccess',
        ];

        $configFiles = $this->getConfigFiles();

        foreach ($files + $configFiles as $absolutePath => $relativePath) {

            if (is_int($absolutePath)) {
                $absolutePath = $this->projectRoot.$relativePath;
            }

            if (!file_exists($absolutePath)) {
                continue;
            }

            $fp = fopen($absolutePath, 'r');
            $fstat = fstat($fp);
            fclose($fp);

            $filesProperties[] = [
                'path' => $relativePath,
                'size' => $fstat['size'],
                'mtime' => $fstat['mtime'],
                'checksum' => md5_file($absolutePath),
            ];
        }

        return $filesProperties;
    }

    /**
     * @return array
     */
    private function getConfigFiles()
    {
        return collect(glob($this->projectRoot.'/app/config/*'))->mapWithKeys(function ($absolutePath) {
            $relativePath = str_replace($this->projectRoot.DIRECTORY_SEPARATOR, '', $absolutePath);

            return [
                $absolutePath => $relativePath,
            ];
        })->toArray();
    }
}
