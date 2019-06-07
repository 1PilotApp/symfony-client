<?php

namespace OnePilot\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /** @var \OnePilot\ClientBundle\Middlewares\Authentication */
    protected $authenticationService;

    /** @var \OnePilot\ClientBundle\Classes\Composer */
    protected $composerService;

    /** @var \OnePilot\ClientBundle\Classes\Files */
    protected $fileService;

    protected function initServices()
    {
        $this->authenticationService = $this->get('one_pilot_client.service.authentication');

        $this->composerService = $this->get('one_pilot_client.service.composer');

        $this->fileService = $this->get('one_pilot_client.service.files');
    }
}
