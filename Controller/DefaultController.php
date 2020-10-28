<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Exceptions\ValidateFailed;
use OnePilot\ClientBundle\Middlewares\Authentication;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    /** @var Authentication */
    protected $authentication;

    protected function checkAuthentication(Request $request)
    {
        try {
            if (empty($this->authentication)) {
                throw ValidateFailed::authenticationNotInjected();
            }

            $this->authentication->handle($request);
        } catch (ValidateFailed $exception) {
            return $exception->render();
        }
    }
}
