<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Exceptions\ValidateFailed;
use OnePilot\ClientBundle\Middlewares\Authentication;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    protected function checkAuthentication(Request $request)
    {
        /** @var Authentication $authentication */
        $authentication = $this->get('one_pilot_client.service.authentication');

        try {
            $authentication->handle($request);
        } catch (ValidateFailed $exception) {
            return $exception->render();
        }
    }
}
