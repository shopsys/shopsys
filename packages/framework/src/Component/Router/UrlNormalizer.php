<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class UrlNormalizer
{
    /**
     * Normalizes URL by removing domain prefix and ensuring proper format
     */
    public static function normalizeUrl(?string $url, DomainConfig $domainConfig): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);
        $domainUrl = rtrim($domainConfig->getUrl(), '/');

        if ($url === $domainUrl) {
            return '/';
        }

        if (str_starts_with($url, $domainUrl . '/')) {
            $url = substr($url, strlen($domainUrl));
        }

        if (str_starts_with($url, 'http://')) {
            return $url;
        }

        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, 'www.')) {
            return 'https://' . $url;
        }

        if (!str_starts_with($url, '/')) {
            $url = '/' . $url;
        }

        return $url;
    }
}
