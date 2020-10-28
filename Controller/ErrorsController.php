<?php

namespace OnePilot\ClientBundle\Controller;

use OnePilot\ClientBundle\Classes\LogsBrowser;
use OnePilot\ClientBundle\Middlewares\Authentication;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ErrorsController extends DefaultController
{
    /** @var */
    protected $browser;

    public function __construct(LogsBrowser $browser, Authentication $authentication)
    {
        $this->browser = $browser;
        $this->authentication = $authentication;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if ($response = $this->checkAuthentication($request)) {
            return $response;
        }

        $this->browser->setPagination($request->get('page', 1), $request->get('per_page', 50));
        $this->browser->setRange($request->get('from'), $request->get('to'));
        $this->browser->setSearch($request->get('search'));
        $this->browser->setLevels($request->get('levels'));

        return new JsonResponse(array_merge([
            'data' => $this->browser->get(),
        ], $this->browser->getPagination()));
    }
}
