<?php

namespace OnePilot\ClientBundle\Classes;

use OnePilot\ClientBundle\Traits\Instantiable;
use Symfony\Component\HttpKernel\Kernel;

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
            '.env',

            // Symfony 3
            'web/app.php',
            'web/app_dev.php',
            'web/.htaccess',

            // Symfony 4
            'public/index.php',
            'public/.htaccess',
        ];

        $configFiles = $this->getConfigFiles();

        foreach ($files + $configFiles as $absolutePath => $relativePath) {

            if (is_int($absolutePath)) {
                $absolutePath = $this->projectRoot . DIRECTORY_SEPARATOR . $relativePath;
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
        $configDirectory = Kernel::MAJOR_VERSION === 4 ? '/config/*' : '/app/config/*';

        $files = [];

        foreach (glob($this->projectRoot . $configDirectory) as $absolutePath) {
            if (is_dir($absolutePath)) {
                continue;
            }

            $files[$absolutePath] = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $absolutePath);
        }

        return $files;
    }
}
