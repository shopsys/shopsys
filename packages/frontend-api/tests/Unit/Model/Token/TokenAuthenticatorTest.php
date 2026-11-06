<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Token;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticatorTest extends TestCase
{
    public function testOnAuthenticationFailureReturnsExpiredTokenErrorWithHttpOkStatus(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();

        $response = $tokenAuthenticator->onAuthenticationFailure(
            new Request(),
            new ExpiredTokenUserMessageException('Token is expired. Please renew.'),
        );

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('expired-token', $responseData['errors'][0]['extensions']['userCode']);
    }

    public function testOnAuthenticationFailureReturnsInvalidTokenErrorWithHttpOkStatus(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();

        $response = $tokenAuthenticator->onAuthenticationFailure(
            new Request(),
            new InvalidTokenUserMessageException('Token is not valid.'),
        );

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('invalid-token', $responseData['errors'][0]['extensions']['userCode']);
    }

    public function testHeaderTokenIsAcceptedRegardlessOfMethodAndOrigin(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();
        $request = Request::create('/graphql/', 'GET');
        $request->headers->set('X-Auth-Token', 'Bearer header-token');

        $this->assertTrue($tokenAuthenticator->supports($request));
        $this->assertSame('header-token', $tokenAuthenticator->getCredentials($request));
    }

    public function testHeaderTokenTakesPrecedenceOverCookieToken(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();
        $request->headers->set('X-Auth-Token', 'Bearer header-token');

        $this->assertSame('header-token', $tokenAuthenticator->getCredentials($request));
    }

    public function testHeaderWithoutBearerPrefixIsNotAccepted(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();
        $request->headers->set('X-Auth-Token', 'header-token');

        $this->assertFalse($tokenAuthenticator->supports($request));
        $this->assertNull($tokenAuthenticator->getCredentials($request));
    }

    public function testCookieTokenIsAcceptedForSameOriginPostWithDomainIdHeader(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();

        $this->assertTrue($tokenAuthenticator->supports($request));
        $this->assertSame('cookie-token', $tokenAuthenticator->getCredentials($request));
    }

    public function testCookieTokenIsAcceptedWhenDomainHostIsCapitalised(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator('https://Shop.Example');
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();

        $this->assertTrue($tokenAuthenticator->supports($request));
    }

    public function testCookieTokenIsAcceptedOnDomainWithDefaultPortOmittedByBrowser(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator('https://shop.example:443/');
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();

        $this->assertTrue($tokenAuthenticator->supports($request));
        $this->assertSame('cookie-token', $tokenAuthenticator->getCredentials($request));
    }

    public function testCookieTokenIsAcceptedOnDomainWithUrlPathAndPort(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator('http://127.0.0.1:8000/sk');
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();
        $request->headers->set('Origin', 'http://127.0.0.1:8000');

        $this->assertTrue($tokenAuthenticator->supports($request));
        $this->assertSame('cookie-token', $tokenAuthenticator->getCredentials($request));
    }

    /**
     * @param callable(\Symfony\Component\HttpFoundation\Request): void $breakRequest
     */
    #[DataProvider('getRequestsNotAllowedToAuthenticateByCookie')]
    public function testCookieTokenIsIgnoredWhenCsrfGuardIsNotSatisfied(callable $breakRequest): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();
        $request = $this->createSameOriginPostRequestWithAccessTokenCookie();
        $breakRequest($request);

        $this->assertFalse($tokenAuthenticator->supports($request));
        $this->assertNull($tokenAuthenticator->getCredentials($request));
    }

    /**
     * @return iterable<string, array{callable(\Symfony\Component\HttpFoundation\Request): void}>
     */
    public static function getRequestsNotAllowedToAuthenticateByCookie(): iterable
    {
        yield 'GET request' => [
            static fn (Request $request) => $request->setMethod('GET'),
        ];

        yield 'missing domain id header' => [
            static fn (Request $request) => $request->headers->remove('X-Domain-Id'),
        ];

        yield 'domain id header of another domain' => [
            static fn (Request $request) => $request->headers->set('X-Domain-Id', '2'),
        ];

        yield 'empty cookie' => [
            static fn (Request $request) => $request->cookies->set('accessToken-1', ''),
        ];

        yield 'missing origin' => [
            static fn (Request $request) => $request->headers->remove('Origin'),
        ];

        yield 'foreign origin' => [
            static fn (Request $request) => $request->headers->set('Origin', 'https://evil.example'),
        ];

        yield 'cookie of another domain' => [static function (Request $request): void {
            $request->cookies->remove('accessToken-1');
            $request->cookies->set('accessToken-2', 'cookie-token');
        }];
    }

    private function createSameOriginPostRequestWithAccessTokenCookie(): Request
    {
        $request = Request::create('/graphql/', 'POST');
        $request->headers->set('X-Domain-Id', '1');
        $request->headers->set('Origin', 'https://shop.example');
        $request->cookies->set('accessToken-1', 'cookie-token');

        return $request;
    }

    private function createTokenAuthenticator(string $domainUrl = 'https://shop.example'): TokenAuthenticator
    {
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(1);
        $domain->method('getUrl')->willReturn($domainUrl);

        return new TokenAuthenticator(
            $this->createStub(TokenFacade::class),
            $this->createStub(FrontendApiUserProvider::class),
            $this->createStub(CustomerUserFacade::class),
            $domain,
        );
    }
}
