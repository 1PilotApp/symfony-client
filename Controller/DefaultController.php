<?php

namespace OnePilot\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

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

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function pingAction(Request $request)
    {
        $this->get('one_pilot_client.service.authentication')->handle($request);

        return new Response("pong");
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validateAction(Request $request)
    {
        $this->get('one_pilot_client.service.authentication')->handle($request);

        return new JsonResponse([
            'core' => $this->getCore(),
            'servers' => $this->getServers(),
            'plugins' => $this->get('one_pilot_client.service.composer')->getPackagesData(),
            'extra' => $this->getExtra(),
            'files' => $this->get('one_pilot_client.service.files')->getFilesProperties(),
        ]);
    }

    /**
     * @return array
     */
    private function getCore()
    {
        $runningVersion = \Symfony\Component\HttpKernel\Kernel::VERSION;
        $symfony = $this->get('one_pilot_client.service.composer')->getLatestPackageVersion('symfony/symfony', $runningVersion);

        return [
            'version' => $runningVersion,
            'new_version' => $symfony['compatible'],
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
            $dbVersion = $this->get('doctrine.dbal.default_connection')->executeQuery("select version() as version")->fetchColumn();
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
            'storage_dir_writable' => is_writable($this->getParameter('kernel.logs_dir')),
            'cache_dir_writable' => is_writable($this->getParameter('kernel.cache_dir')),
        ];

        foreach (self::CONFIGS_TO_MONITOR as $config) {
            try {
                $extra[$config] = $this->getParameter($config);
            } catch (InvalidArgumentException $ex) {
                $extra[$config] = 'undefined';
            }
        }

        return $extra;
    }
}
