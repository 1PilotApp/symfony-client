<?php

namespace OnePilot\ClientBundle\Tests\Integration;

use OnePilot\ClientBundle\Tests\TestCase;

class MailTesterTest extends TestCase
{
    /** @test */
    public function response_is_success()
    {
        $this->client->request('POST', '/onepilot/mail-tester', [
            'email' => 'destination@example.com',
        ], [], $this->authenticationHeaders());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
