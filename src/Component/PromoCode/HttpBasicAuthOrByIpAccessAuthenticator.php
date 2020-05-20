<?php

declare(strict_types=1);

namespace App\Component\PromoCode;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;

class HttpBasicAuthOrByIpAccessAuthenticator
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    private $allowedIps;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var string
     */
    private $user;

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param array $promoCodeManagePageConfig
     */
    public function __construct(Logger $logger, array $promoCodeManagePageConfig)
    {
        $this->user = $promoCodeManagePageConfig['user'];
        $this->pass = $promoCodeManagePageConfig['pass'];
        $this->allowedIps = $promoCodeManagePageConfig['allowed_ips'];
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function verifyAccess(Request $request): bool
    {
        $validPasswords = [$this->user => $this->pass];
        $validUsers = array_keys($validPasswords);
        $user = $request->headers->get('php-auth-user');
        $pass = $request->headers->get('php-auth-pw');

        if (in_array($request->getClientIp(), $this->allowedIps, true)) {
            return true;
        } else {
            return (in_array($user, $validUsers, true)) && ($pass === $validPasswords[$user]);
        }
    }
}
