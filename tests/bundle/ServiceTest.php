<?php

namespace Acme\StandaloneBundle\Tests;

use OnePilot\ClientBundle\Tests\TestCase;

class ServiceTest extends TestCase
{
    public function testServicesAreDefinedInContainer()
    {
        $this->assertInstanceOf(
            'OnePilot\ClientBundle\Classes\Composer',
            $this->container->get('one_pilot_client.service.composer')
        );

        $this->assertInstanceOf(
            'OnePilot\ClientBundle\Classes\Files',
            $this->container->get('one_pilot_client.service.files')
        );

        $this->assertInstanceOf(
            'OnePilot\ClientBundle\Classes\ComposerPackageDetector',
            $this->container->get('one_pilot_client.service.package_detector')
        );

        $this->assertInstanceOf(
            'OnePilot\ClientBundle\Middlewares\Authentication',
            $this->container->get('OnePilot\ClientBundle\Middlewares\Authentication')
        );
    }
}
