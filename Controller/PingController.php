<?php

namespace OnePilot\ClientBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PingController extends DefaultController
{
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
