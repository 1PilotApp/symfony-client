<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Classes\Composer;
use OnePilot\ClientBundle\Classes\Files;
use OnePilot\ClientBundle\Classes\LogsOverview;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class ValidateController extends DefaultController
{
    const CONFIGS_TO_MONITOR = [
        'database_host',
        'database_port',
        'database_name',
        'database_user',
        'mailer_transport',
        'mailer_host',
        'mailer_user',
    ];

    /** @var Composer */
    protected $composer;

    /** @var Files */
    protected $files;

    /** @var LogsOverview */
    protected $logsOverview;

    public function __construct(Composer $composer, Files $files, LogsOverview $logsOverview)
    {
        $this->composer = $composer;
        $this->files = $files;
        $this->logsOverview = $logsOverview;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if ($response = $this->checkAuthentication($request)) {
            return $response;
        }

        return new JsonResponse([
            'core'    => $this->getCore(),
            'servers' => $this->getServers(),
            'plugins' => $this->composer->getPackagesData(),
            'extra'   => $this->getExtra(),
            'files'   => $this->files->getFilesProperties(),
            'errors'  => $this->errorsOverview(),
        ]);
    }

    /**
     * @return array
     */
    private function getCore()
    {
        $symfony = $this->composer->getLatestPackageVersion('symfony/symfony', Kernel::VERSION);

        return [
            'version'                => Kernel::VERSION,
            'new_version'            => $symfony['compatible'],
            'last_available_version' => $symfony['available'],
        ];
    }

    /**
     * Get system versions
     *
     * @return array
     */
    private function getServers()
    {
        $serverWeb = $_SERVER['SERVER_SOFTWARE'] ?? getenv('SERVER_SOFTWARE') ?? null;

        try {
            $dbVersion = $this->get('doctrine.dbal.default_connection')
                ->executeQuery("select version() as version")
                ->fetchColumn();
        } catch (\Exception $e) {
            $dbVersion = null;
        }

        return [
            'php'   => phpversion(),
            'web'   => $serverWeb,
            'mysql' => $dbVersion,
        ];
    }

    /**
     * @return array
     */
    private function getExtra()
    {
        $extra = [
            'storage_dir_writable' => is_writable($this->getParameter('kernel.logs_dir')),
            'cache_dir_writable'   => is_writable($this->getParameter('kernel.cache_dir')),
            'app.env'              => $this->getParameter('kernel.environment'),
        ];

        foreach (self::CONFIGS_TO_MONITOR as $config) {
            try {
                $extra[$config] = $this->getParameter($config);
            } catch (InvalidArgumentException $ex) {
                $extra[$config] = null;
            }
        }

        return $extra;
    }

    private function errorsOverview()
    {
        try {
            return $this->logsOverview->get();
        } catch (\Exception $e) {
        }
    }
}
