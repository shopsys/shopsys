<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation\Exception;

use App\Environment;
use Exception;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundRedirectToStorefrontException extends NotFoundHttpException
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        $isDev = class_exists('App\Environment') && Environment::getEnvironment() === EnvironmentType::DEVELOPMENT;

        $headers = $isDev ? [] : ['X-Accel-Redirect' => $this->getRedirectLocation()];

        parent::__construct($message, $previous, 0, $headers);
    }

    protected function getRedirectLocation(): string
    {
        $location = '@storefront';

        // avoid storefront 404 page for CDN requests
        if (array_key_exists('HTTP_CDN_VSHOSTING_REAL_IP', $_SERVER) || array_key_exists('HTTP_CDN_VSHOSTING_REAL_IP_IMG', $_SERVER)) {
            $location = '@404';
        }

        return $location;
    }
}
