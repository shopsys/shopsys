<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainSubscriber;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdminUrlProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AdminDomainSubscriberTest extends TestCase
{
    public const string FIRST_DOMAIN_BASE_URL = 'http://example.com:8080';

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createFirstDomainConfig(): DomainConfig
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            self::FIRST_DOMAIN_BASE_URL,
            'First Domain',
            'en',
            $defaultTimeZone,
            self::FIRST_DOMAIN_BASE_URL,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createFirstDomainConfigWithPostfix(): DomainConfig
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            self::FIRST_DOMAIN_BASE_URL . '/en',
            'First Domain EN',
            'en',
            $defaultTimeZone,
            self::FIRST_DOMAIN_BASE_URL,
            DomainConfig::TYPE_B2C,
            true,
            '/en',
        );
    }

    public function testOnKernelRequestIgnoresNonAdminContext(): void
    {
        $adminUrlProvider = $this->createMock(AdminUrlProvider::class);
        $domain = $this->createMock(Domain::class);
        $contextResolver = $this->createMock(ContextResolverInterface::class);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domain, $contextResolver);

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $contextResolver
            ->expects($this->once())
            ->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(false);

        $domain
            ->expects($this->never())
            ->method('getDomainConfigById');

        $subscriber->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    /**
     * @dataProvider adminRedirectDataProvider
     * @param string $requestUri
     * @param string $pathInfo
     * @param string $expectedRedirectUrl
     * @param string|null $queryString
     */
    public function testOnKernelRequestRedirectsToFirstDomain(
        string $requestUri,
        string $pathInfo,
        string $expectedRedirectUrl,
        ?string $queryString = null,
    ): void {
        $adminUrlProvider = $this->createMock(AdminUrlProvider::class);
        $adminUrlProvider
            ->method('getAdminUrl')
            ->willReturn('admin');

        $domain = $this->createMock(Domain::class);
        $domain
            ->method('getDomainConfigById')
            ->with(Domain::FIRST_DOMAIN_ID)
            ->willReturn($this->createFirstDomainConfig());

        $contextResolver = $this->createMock(ContextResolverInterface::class);
        $contextResolver
            ->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(true);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domain, $contextResolver);

        $request = $this->createMock(Request::class);
        $request
            ->method('getUri')
            ->willReturn($requestUri);
        $request
            ->method('getPathInfo')
            ->willReturn($pathInfo);
        $request
            ->method('getQueryString')
            ->willReturn($queryString);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($expectedRedirectUrl, $response->getTargetUrl());
    }

    /**
     * @return array<string, array{requestUri: string, pathInfo: string, expectedRedirectUrl: string, queryString: string|null}>
     */
    public static function adminRedirectDataProvider(): array
    {
        return [
            'basic admin path' => [
                'requestUri' => 'http://other-domain.com/admin',
                'pathInfo' => '/admin',
                'expectedRedirectUrl' => self::FIRST_DOMAIN_BASE_URL . '/admin',
                'queryString' => null,
            ],
            'admin path with subpath' => [
                'requestUri' => 'http://other-domain.com/admin/users',
                'pathInfo' => '/admin/users',
                'expectedRedirectUrl' => self::FIRST_DOMAIN_BASE_URL . '/admin/users',
                'queryString' => null,
            ],
            'admin path with query string' => [
                'requestUri' => 'http://other-domain.com/admin/products?page=2',
                'pathInfo' => '/admin/products',
                'expectedRedirectUrl' => self::FIRST_DOMAIN_BASE_URL . '/admin/products?page=2',
                'queryString' => 'page=2',
            ],
            'admin path with complex query' => [
                'requestUri' => 'http://other-domain.com/admin/orders?status=new&sort=date',
                'pathInfo' => '/admin/orders',
                'expectedRedirectUrl' => self::FIRST_DOMAIN_BASE_URL . '/admin/orders?status=new&sort=date',
                'queryString' => 'status=new&sort=date',
            ],
        ];
    }

    public function testOnKernelRequestWithFirstDomainPostfixRedirectsToBaseUrl(): void
    {
        $adminUrlProvider = $this->createMock(AdminUrlProvider::class);
        $adminUrlProvider
            ->method('getAdminUrl')
            ->willReturn('admin');

        $domain = $this->createMock(Domain::class);
        $domain
            ->method('getDomainConfigById')
            ->with(Domain::FIRST_DOMAIN_ID)
            ->willReturn($this->createFirstDomainConfigWithPostfix());

        $contextResolver = $this->createMock(ContextResolverInterface::class);
        $contextResolver
            ->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(true);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domain, $contextResolver);

        $request = $this->createMock(Request::class);
        $request
            ->method('getUri')
            ->willReturn('http://other-domain.com/admin/users');
        $request
            ->method('getPathInfo')
            ->willReturn('/admin/users');
        $request
            ->method('getQueryString')
            ->willReturn(null);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        // Should redirect to base URL without postfix - admin is always at base domain
        $this->assertEquals(self::FIRST_DOMAIN_BASE_URL . '/admin/users', $response->getTargetUrl());
    }

    public function testOnKernelRequestDoesNotRedirectWhenAlreadyOnCorrectUrl(): void
    {
        $adminUrlProvider = $this->createMock(AdminUrlProvider::class);
        $adminUrlProvider
            ->method('getAdminUrl')
            ->willReturn('admin');

        $domain = $this->createMock(Domain::class);
        $domain
            ->method('getDomainConfigById')
            ->with(Domain::FIRST_DOMAIN_ID)
            ->willReturn($this->createFirstDomainConfig());

        $contextResolver = $this->createMock(ContextResolverInterface::class);
        $contextResolver
            ->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(true);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domain, $contextResolver);

        $request = $this->createMock(Request::class);
        $request
            ->method('getUri')
            ->willReturn(self::FIRST_DOMAIN_BASE_URL . '/admin/users');
        $request
            ->method('getPathInfo')
            ->willReturn('/admin/users');
        $request
            ->method('getQueryString')
            ->willReturn(null);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Should not set any response since request is already on correct URL
        $this->assertNull($event->getResponse());
    }
}
