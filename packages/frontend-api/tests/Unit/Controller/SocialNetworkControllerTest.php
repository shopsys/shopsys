<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrontendApiBundle\Controller\SocialNetworkController;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;
use Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkFacade;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SocialNetworkControllerTest extends TestCase
{
    public function testSocialLoginStoresRefreshTokenInProtectedCookie(): void
    {
        $response = new Response();
        $tokens = (new TokensDataFactory())->create('access-token', 'refresh-token');
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
        $tokens = (new TokensDataFactory())->create('access-token', 'refresh-token');
        $controller = new TestableSocialNetworkController();

        $controller->setTokenCookiesForTest($response, $tokens, 1);

        $cookies = $this->getCookiesByName($response);
        $this->assertFalse($cookies['refreshTokenPresent-1']->isHttpOnly());
        $this->assertSame('1', $cookies['refreshTokenPresent-1']->getValue());
        $this->assertFalse($cookies['accessToken-1']->isHttpOnly());
    }

    public function testSuccessfulSocialLoginRedirectIncludesLoginType(): void
    {
        $generatedParameters = [];
        $domainRouterStub = $this->createStub(DomainRouter::class);
        $domainRouterStub
            ->method('generate')
            ->willReturnCallback(function (string $route, array $parameters = []) use (&$generatedParameters): string {
                if ($route === 'front_social_network_login_page') {
                    $generatedParameters = $parameters;
                }

                return '/';
            });
        $domainRouterFactoryStub = $this->createStub(DomainRouterFactory::class);
        $domainRouterFactoryStub->method('getRouter')->willReturn($domainRouterStub);
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getId')->willReturn(1);
        $controller = new TestableSocialNetworkRedirectController(
            $this->createStub(SocialNetworkFacade::class),
            $domainStub,
            $domainRouterFactoryStub,
        );
        $request = new Request();
        $request->setSession($this->createStub(SessionInterface::class));

        $controller->getRefererUrlForTest($request, 'google');

        $this->assertSame('google', $generatedParameters['socialNetwork']);
        $this->assertArrayNotHasKey('exceptionType', $generatedParameters);
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

final class TestableSocialNetworkRedirectController extends SocialNetworkController
{
    public function getRefererUrlForTest(Request $request, string $type): string
    {
        return $this->getRefererUrl($request, $type, false);
    }
}
