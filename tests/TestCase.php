<?php

namespace OnePilot\ClientBundle\Tests;

use OnePilot\ClientBundle\Classes\FakePackageDetector;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

abstract class TestCase extends BaseTestCase
{
    use SetUpTrait;

    /** @var Kernel */
    protected $kernel;

    /** @var string $privateKey */
    protected $privateKey = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var string $timestamp */
    protected $timestamp;

    /** @var string $hash */
    protected $hash;

    /** @var ContainerInterface $container */
    protected $container;

    /** @var Client */
    protected $client;

    /**
     * @see setUp()
     */
    protected function internalSetUp()
    {
        $this->setTimestamp();
        $this->kernel = new \AppKernel('test', true);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();
        $this->client = $this->container->get('test.client');

        FakePackageDetector::setPackagesFromPath(__DIR__ . '/data/composer/installed-packages-light.json');
        FakePackageDetector::generatePackagesConstraints();
    }

    /**
     * Set timestamp and regenerate hash
     *
     * @param null $timestamp
     */
    protected function setTimestamp($timestamp = null)
    {
        $this->timestamp = $timestamp ?? time();

        $this->hash = $this->generateAuthenticationHash($this->privateKey, $this->timestamp);
    }

    /**
     * @param string $privateKey
     * @param int    $timestamp
     *
     * @return string Hash
     */
    protected function generateAuthenticationHash($privateKey, $timestamp)
    {
        return hash_hmac('sha256', $timestamp, $privateKey);
    }

    /**
     * @return array
     */
    protected function authenticationHeaders()
    {
        return [
            'HTTP_HASH' => $this->hash,
            'HTTP_STAMP' => $this->timestamp,
        ];
    }
}
