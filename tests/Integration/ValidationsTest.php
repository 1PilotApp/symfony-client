<?php

namespace OnePilot\ClientBundle\Tests\Integration;

use OnePilot\ClientBundle\Classes\FakePackageDetector;
use OnePilot\ClientBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ValidationsTest extends TestCase
{
    /** @var Response */
    private static $response;

    public function setUp()
    {
        parent::setUp();

        if (empty(self::$response)) {
            $this->client->request('GET', '/onepilot/validate', [], [], $this->authenticationHeaders());
            self::$response = $this->client->getResponse();
        }
    }

    /** @test */
    public function response_is_success()
    {
        $this->assertEquals(200, self::$response->getStatusCode());
    }

    /** @test */
    public function core_and_php_versions_are_right()
    {
        $core = $this->getDecodedData('core');
        $servers = $this->getDecodedData('servers');

        $this->assertEquals($this->kernel::VERSION, $core['version']);

        $this->assertEquals(phpversion(), $servers['php']);
    }

    /** @test */
    public function extra_parameters()
    {
        $extra = $this->getDecodedData('extra');

        $this->assertEquals($this->container->getParameter('kernel.environment'), $extra['app.env']);
    }

    /**
     * @param string|null $section
     *
     * @return array
     */
    private function getDecodedData($section = null)
    {
        $validationData = json_decode(self::$response->getContent(), true);

        if (empty($section)) {
            return $validationData;
        }

        if (empty($validationData[$section])) {
            return [];
        }

        return $validationData[$section];
    }
}
