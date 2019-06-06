<?php

namespace OnePilot\ClientBundle\Controller;

use Exception;
use OnePilot\ClientBundle\Exceptions\ValidateFailed;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MailTesterController extends DefaultController
{
    /** @var Request */
    private $request;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function send(Request $request)
    {
        $this->initServices();
        $this->request = $request;

        try {
            $this->authenticationService->handle($request);
        } catch (ValidateFailed $exception) {
            return $exception->render();
        }

        if (empty($email = $request->get('email'))) {
            return new JsonResponse([
                'message' => 'Email parameter is missing',
                'status'  => 400,
                'data'    => [],
            ], 400);
        }

//        @todo check if one_pilot_mail_from_address parameter is defined
//        if (empty($this->get('one_pilot_mail_from_address'))) {
//            return new JsonResponse([
//                'message' => '`one_pilot_mail_from_address` parameter not defined',
//                'status'  => 400,
//                'data'    => [],
//            ], 400);
//        }

        try {
            /** @var Swift_Mailer $mailer */
            $mailer = $this->get('swiftmailer.mailer.default');
        } catch (ServiceNotFoundException $e) {
            return new JsonResponse([
                'message' => 'SwiftMailer not installed',
                'status'  => 500,
                'data'    => [],
            ], 400);
        }

        try {
            $numberRecipients = $this->sendEmail($email, $mailer);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Error when sending email',
                'status'  => 500,
                'data'    => [
                    'previous' => [
                        'message' => $e->getMessage(),
                    ],
                ],
            ], 500);
        }

        if ($numberRecipients < 1) {
            return new JsonResponse([
                'message' => 'Error when sending email (message not sended)',
                'status'  => 500,
            ], 500);
        }

        return new JsonResponse(['message' => 'Sent']);
    }

    /**
     * @param               $email
     * @param Swift_Mailer  $mailer
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    protected function sendEmail($email, $mailer)
    {
        $siteUrl = $this->request->getSchemeAndHttpHost();

        // InvalidArgumentException : Malformed UTF-8 characters, possibly incorrectly encoded
        $message = (new Swift_Message('Test send by 1Pilot.io for ensure emails are properly sent'))
            ->setFrom("from@example.com") // @todo replace by value of one_pilot_mail_from_address
            ->setTo($email)
            ->setBody(<<<EOF
This email was automatically sent by the 1Pilot Client installed on $siteUrl.

Ground control to Major Tom
Ground control to Major Tom
Take your protein pills and put your helmet on

Ground control to Major Tom
(10, 9, 8, 7)
Commencing countdown, engines on
(6, 5, 4, 3)
Check ignition, and may God's love be with you
(2, 1, liftoff)

This is ground control to Major Tom,

You've really made the grade
And the papers want to know whose shirts you wear
Now it's time to leave the capsule if you dare

This is Major Tom to ground control
I'm stepping through the door
And I'm floating in the most of peculiar way
And the stars look very different today

For here am I sitting in a tin can
Far above the world
Planet Earth is blue, and there's nothing I can do

Though I'm past 100,000 miles
I'm feeling very still
And I think my spaceship knows which way to go
Tell my wife I love her very much, she knows

Ground control to Major Tom,
Your circuit's dead, there's something wrong
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you...

Here am I floating round my tin can
Far above the moon
Planet Earth is blue, and there's nothing I can do...

Ground control to Major Tom,
Your circuit's dead, there's something wrong
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you...

Space Oddity
David Bowie
EOF
            );

        return $mailer->send($message);
    }
}
