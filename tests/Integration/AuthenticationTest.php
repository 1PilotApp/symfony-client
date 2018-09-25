<?php

namespace OnePilot\ClientBundle\Tests\Integration;

use OnePilot\ClientBundle\Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function it_will_fail_when_call_validate_without_authentication_headers()
    {
        $this->client->request('GET', '/onepilot/validate');
        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([
            'message' => "The request did not contain a header named `HTTP_HASH`.",
            'status' => 400,
            'data' => [],
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_will_fail_when_no_authentication_headers_are_set()
    {
        $this->client->request('GET', '/onepilot/ping');
        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([
            'message' => "The request did not contain a header named `HTTP_HASH`.",
            'status' => 400,
            'data' => [],
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_will_fail_when_using_past_stamp()
    {
        $this->setTimestamp(1500000000);

        $this->client->request('GET', '/onepilot/ping', [], [], $this->authenticationHeaders());
        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([
            'message' => "The timestamp found in the header is invalid",
            'status' => 400,
            'data' => [],
        ], json_decode($response->getContent(), true));

    }

    /** @test */
    public function it_will_fail_when_using_empty_stamp()
    {
        $this->setTimestamp(1500000000);

        $this->client->request('GET', '/onepilot/ping', [], [], $this->authenticationHeaders());
        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([
            'message' => "The timestamp found in the header is invalid",
            'status' => 400,
            'data' => [],
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_will_work_when_using_past_stamp_with_skip_time_stamp_validation_enabled()
    {
        $this->setTimestamp(1500000000);

        $this->container->get('one_pilot_client.service.authentication')->setSkipTimeValidation(true);

        $this->client->request('GET', '/onepilot/ping', [], [], $this->authenticationHeaders());
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([
            'message' => "pong",
        ], json_decode($response->getContent(), true));
    }

    /** @test */
    public function it_will_work_when_using_valid_stamp_and_hash()
    {
        $this->client->request('GET', '/onepilot/ping', [], [], $this->authenticationHeaders());
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([
            'message' => "pong",
        ], json_decode($response->getContent(), true));
    }
}
