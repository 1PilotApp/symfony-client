<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Exceptions\ValidateFailed;
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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $this->initServices();

        try {
            $this->authenticationService->handle($request);
        } catch (ValidateFailed $exception) {
            return $exception->render();
        }

        return new JsonResponse([
            'core'    => $this->getCore(),
            'servers' => $this->getServers(),
            'plugins' => $this->composerService->getPackagesData(),
            'extra'   => $this->getExtra(),
            'files'   => $this->fileService->getFilesProperties(),
        ]);
    }

    /**
     * @return array
     */
    private function getCore()
    {
        $symfony = $this->composerService->getLatestPackageVersion('symfony/symfony', Kernel::VERSION);

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
}
