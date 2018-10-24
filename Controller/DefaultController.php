<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Exceptions\ValidateFailed;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
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

    /** @var \OnePilot\ClientBundle\Middlewares\Authentication */
    private $authenticationService;

    /** @var \OnePilot\ClientBundle\Classes\Composer */
    private $composerService;

    /** @var \OnePilot\ClientBundle\Classes\Files */
    private $fileService;

    public function __construct()
    {
        $this->authenticationService = $this->get('one_pilot_client.service.authentication');

        $this->composerService = $this->get('one_pilot_client.service.composer');

        $this->fileService = $this->get('one_pilot_client.service.files');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function ping(Request $request)
    {
        try {
            $this->authenticationService->handle($request);
        } catch (ValidateFailed $exception) {
            return $exception->render();
        }

        return new JsonResponse(["message" => "pong"]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validate(Request $request)
    {
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
        $runningVersion = \Symfony\Component\HttpKernel\Kernel::VERSION;
        $symfony = $this->composerService->getLatestPackageVersion('symfony/symfony', $runningVersion);

        return [
            'version'                => $runningVersion,
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
