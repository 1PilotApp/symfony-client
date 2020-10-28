<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Middlewares\Authentication;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PingController extends DefaultController
{
    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if ($response = $this->checkAuthentication($request)) {
            return $response;
        }

        return new JsonResponse(["message" => "pong"]);
    }
}
