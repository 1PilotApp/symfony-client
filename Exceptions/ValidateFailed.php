<?php

namespace OnePilot\ClientBundle\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ValidateFailed extends Exception
{
    /**
     * @return ValidateFailed
     */
    public static function authenticationNotInjected()
    {
        return new static('1Pilot authentication service was not properly injected');
    }

    /**
     * @return ValidateFailed
     */
    public static function missingSignature()
    {
        return new static('The request did not contain a header named `HTTP_HASH`.');
    }

    /**
     * @param string $signature
     *
     * @return ValidateFailed
     */
    public static function invalidSignature($signature)
    {
        return new static("The signature `{$signature}` found in the header is invalid");
    }

    /**
     * @return ValidateFailed
     */
    public static function invalidTimestamp()
    {
        return new static("The timestamp found in the header is invalid");
    }

    /**
     * @return ValidateFailed
     */
    public static function signingPrivateKeyNotSet()
    {
        return new static('The private key is not set. Make sure that the `onepilot.private_key` config key is set.');
    }

    /**
     * @return Response
     */
    public function render()
    {
        return new JsonResponse([
            'message' => $this->getMessage(),
            'status' => 400,
            'data' => [],
        ], 400);
    }
}
