<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Exceptions\ValidateFailed;
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
        $this->initServices();

        try {
            $this->authenticationService->handle($request);
        } catch (ValidateFailed $exception) {
            return $exception->render();
        }

        return new JsonResponse(["message" => "pong"]);
    }
}
