<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Classes\Composer;
use OnePilot\ClientBundle\Classes\Files;
use OnePilot\ClientBundle\Classes\LogsOverview;
use OnePilot\ClientBundle\Middlewares\Authentication;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class ValidateController extends DefaultController
{
    const CONFIGS_TO_MONITOR = [
        'database_host',
    ];

    /** @var Composer */
    protected $composer;

    /** @var Files */
    protected $files;

    /** @var LogsOverview */
    protected $logsOverview;

    /** @var ParameterBagInterface */
    protected $params;

    public function __construct(
        Composer $composer,
        Files $files,
        LogsOverview $logsOverview,
        ParameterBagInterface $params,
        Authentication $authentication
    ) {
        $this->composer = $composer;
        $this->files = $files;
        $this->logsOverview = $logsOverview;
        $this->params = $params;
        $this->authentication = $authentication;
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
            'core' => $this->getCore(),
            'servers' => $this->getServers(),
            'plugins' => $this->composer->getPackagesData(),
            'extra' => $this->getExtra(),
            'files' => $this->files->getFilesProperties(),
            'errors' => $this->errorsOverview(),
        ]);
    }

    /**
     * @return array
     */
    private function getCore()
    {
        $symfony = $this->composer->getNewCompatibleAndAvailableVersionsNumber(
            'symfony/symfony', Kernel::VERSION
        );

        return [
            'version' => Kernel::VERSION,
            'new_version' => $symfony['compatible'] ?? null,
            'last_available_version' => $symfony['available'] ?? null,
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
            $dbVersion = $this->container->get('doctrine.dbal.default_connection')
                ->executeQuery("select version() as version")
                ->fetchColumn();
        } catch (\Exception $e) {
            $dbVersion = null;
        }

        return [
            'php' => phpversion(),
            'web' => $serverWeb,
            'mysql' => $dbVersion,
        ];
    }

    /**
     * @return array
     */
    private function getExtra()
    {
        $extra = [
            'storage_dir_writable' => is_writable($this->params->get('kernel.logs_dir')),
            'cache_dir_writable' => is_writable($this->params->get('kernel.cache_dir')),
            'app.env' => $this->params->get('kernel.environment'),
        ];

        foreach (self::CONFIGS_TO_MONITOR as $config) {
            try {
                $extra[$config] = $this->params->get($config);
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
