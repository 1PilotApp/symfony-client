<?php

namespace OnePilot\ClientBundle\Tests\Integration;

use OnePilot\ClientBundle\Tests\TestCase;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\HttpFoundation\Response;

class MailTesterTest extends TestCase
{
    /** @var Response */
    private static $response;

    /** @var MessageDataCollector */
    private static $mailCollector;

    public function internalSetUp()
    {
        parent::internalSetUp();

        if (empty(self::$response)) {
            $this->client->enableProfiler();

            $this->client->request('POST', '/onepilot/mail-tester', [
                'email' => 'destination@example.com',
            ], [], $this->authenticationHeaders());

            self::$response = $this->client->getResponse();
            self::$mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        }
    }

    /** @test */
    public function response_is_success()
    {
        $this->assertEquals(200, self::$response->getStatusCode());
    }

    /** @test */
    public function mail_has_been_properly_send()
    {
        $this->assertEquals(1, self::$mailCollector->getMessageCount());

        $mail = self::$mailCollector->getMessages()[0];

        $this->assertInstanceOf('Swift_Message', $mail);
        $this->assertEquals('Test send by 1Pilot.io for ensure emails are properly sent', $mail->getSubject());
        $this->assertEquals('unit-tests@example.com', key($mail->getFrom()));
        $this->assertEquals('destination@example.com', key($mail->getTo()));
        $this->assertStringStartsWith(
            'This email was automatically sent by the 1Pilot Client installed on http://localhost.',
            $mail->getBody()
        );
    }
}
