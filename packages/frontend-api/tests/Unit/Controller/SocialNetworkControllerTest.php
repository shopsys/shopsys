<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Shopsys\FrontendApiBundle\Controller\SocialNetworkController;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class SocialNetworkControllerTest extends TestCase
{
    public function testSocialLoginStoresRefreshTokenInProtectedCookie(): void
    {
        $response = new Response();
        $tokens = new TokensData('access-token', 'refresh-token');
        $controller = new TestableSocialNetworkController();

        $controller->setTokenCookiesForTest($response, $tokens, 3);

        $cookies = $this->getCookiesByName($response);
        $refreshTokenCookie = $cookies['refreshToken-3'];
        $this->assertSame('refresh-token', $refreshTokenCookie->getValue());
        $this->assertTrue($refreshTokenCookie->isHttpOnly());
        $this->assertTrue($refreshTokenCookie->isSecure());
        $this->assertSame(Cookie::SAMESITE_LAX, $refreshTokenCookie->getSameSite());
        $this->assertSame('/', $refreshTokenCookie->getPath());
        $this->assertGreaterThanOrEqual(3600 * 24 * 14 - 2, $refreshTokenCookie->getMaxAge());
    }

    public function testSocialLoginExposesOnlyNonSensitiveRefreshTokenMarkerToJavaScript(): void
    {
        $response = new Response();
        $tokens = new TokensData('access-token', 'refresh-token');
        $controller = new TestableSocialNetworkController();

        $controller->setTokenCookiesForTest($response, $tokens, 1);

        $cookies = $this->getCookiesByName($response);
        $this->assertFalse($cookies['refreshTokenPresent-1']->isHttpOnly());
        $this->assertSame('1', $cookies['refreshTokenPresent-1']->getValue());
        $this->assertFalse($cookies['accessToken-1']->isHttpOnly());
    }

    /**
     * @return array<string, \Symfony\Component\HttpFoundation\Cookie>
     */
    private function getCookiesByName(Response $response): array
    {
        $cookiesByName = [];

        foreach ($response->headers->getCookies() as $cookie) {
            $cookiesByName[$cookie->getName()] = $cookie;
        }

        return $cookiesByName;
    }
}

final class TestableSocialNetworkController extends SocialNetworkController
{
    public function __construct()
    {
    }

    public function setTokenCookiesForTest(Response $response, TokensData $tokens, int $domainId): void
    {
        $this->setTokenCookies($response, $tokens, $domainId);
    }
}
