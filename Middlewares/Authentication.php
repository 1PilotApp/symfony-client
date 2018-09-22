<?php

namespace OnePilot\ClientBundle\Middlewares;

use Closure;
use OnePilot\ClientBundle\Exceptions\ValidateFailed;
use Symfony\Component\HttpFoundation\Request;

class Authentication
{
    /** @var string */
    private $privateKey;

    /** @var bool */
    private $skipTimeStampValidation;

    /**
     * Authentication constructor.
     *
     * @param string $privateKey
     * @param string $skipTimeStampValidation
     */
    public function __construct($privateKey, $skipTimeStampValidation)
    {
        $this->privateKey = $privateKey;
        $this->skipTimeStampValidation = $skipTimeStampValidation;
    }

    /**
     * @param Request $request
     *
     * @return bool
     * @throws ValidateFailed
     */
    public function handle(Request $request)
    {
        $signature = $request->headers->get('hash');
        $stamp = $request->headers->get('stamp');

        if (!$signature) {
            throw ValidateFailed::missingSignature();
        }

        if (!$this->isValidateTimeStamp($stamp)) {
            throw ValidateFailed::invalidTimestamp();
        }

        if (!$this->isValidSignature($signature, $stamp)) {
            throw ValidateFailed::invalidSignature($signature);
        }

        return true;
    }

    /**
     * @param string $signature
     * @param string $stamp
     *
     * @return bool
     * @throws ValidateFailed
     */
    protected function isValidSignature(string $signature, string $stamp)
    {
        if (empty($this->privateKey)) {
            throw ValidateFailed::signingPrivateKeyNotSet();
        }

        $computedSignature = hash_hmac('sha256', $stamp, $this->privateKey);

        return hash_equals($signature, $computedSignature);
    }

    /**
     * Validate timestamp. The meaning of this check is to enhance security by
     * making sure any token can only be used in a short period of time.
     *
     * @param integer $stamp
     *
     * @return boolean
     */
    private function isValidateTimeStamp($stamp)
    {
        if ($this->skipTimeStampValidation) {
            return true;
        }

        if (($stamp > time() - 360) && ($stamp < time() + 360)) {
            return true;
        }

        return false;
    }
}
