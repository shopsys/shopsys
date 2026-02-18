<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Domain\AdminDomainSubscriber;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administration\AdminUrlProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class AdminDomainSubscriberTest extends TestCase
{
    private function createFirstDomainConfig(): DomainConfig
    {
        return DomainConfigHelper::getDomainConfig();
    }

    private function createFirstDomainConfigWithPostfix(): DomainConfig
    {
        return DomainConfigHelper::getDomainConfig(
            url: DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/en',
            name: 'First Domain EN',
            locale: 'en',
            postfix: '/en',
        );
    }

    public function testOnKernelRequestIgnoresNonAdminContext(): void
    {
        $adminUrlProvider = $this->createStub(AdminUrlProvider::class);
        $domain = $this->createMock(Domain::class);
        $contextResolver = $this->createMock(ContextResolverInterface::class);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domain, $contextResolver);

        $request = new Request();
        $kernel = $this->createStub(HttpKernelInterface::class);
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

    #[DataProvider('adminRedirectDataProvider')]
    public function testOnKernelRequestRedirectsToFirstDomain(
        string $requestUri,
        string $pathInfo,
        string $expectedRedirectUrl,
        ?string $queryString = null,
    ): void {
        $adminUrlProvider = $this->createStub(AdminUrlProvider::class);
        $adminUrlProvider
            ->method('getAdminUrl')
            ->willReturn('admin');

        $domainMock = $this->createMock(Domain::class);
        $domainMock
            ->expects($this->any())->method('getDomainConfigById')
            ->with(Domain::FIRST_DOMAIN_ID)
            ->willReturn($this->createFirstDomainConfig());

        $contextResolverMock = $this->createMock(ContextResolverInterface::class);
        $contextResolverMock
            ->expects($this->any())->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(true);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domainMock, $contextResolverMock);

        $request = $this->createStub(Request::class);
        $request
            ->method('getUri')
            ->willReturn($requestUri);
        $request
            ->method('getPathInfo')
            ->willReturn($pathInfo);
        $request
            ->method('getQueryString')
            ->willReturn($queryString);

        $kernel = $this->createStub(HttpKernelInterface::class);
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
                'expectedRedirectUrl' => DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/admin',
                'queryString' => null,
            ],
            'admin path with subpath' => [
                'requestUri' => 'http://other-domain.com/admin/users',
                'pathInfo' => '/admin/users',
                'expectedRedirectUrl' => DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/admin/users',
                'queryString' => null,
            ],
            'admin path with query string' => [
                'requestUri' => 'http://other-domain.com/admin/products?page=2',
                'pathInfo' => '/admin/products',
                'expectedRedirectUrl' => DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/admin/products?page=2',
                'queryString' => 'page=2',
            ],
            'admin path with complex query' => [
                'requestUri' => 'http://other-domain.com/admin/orders?status=new&sort=date',
                'pathInfo' => '/admin/orders',
                'expectedRedirectUrl' => DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/admin/orders?status=new&sort=date',
                'queryString' => 'status=new&sort=date',
            ],
        ];
    }

    public function testOnKernelRequestWithFirstDomainPostfixRedirectsToBaseUrl(): void
    {
        $adminUrlProvider = $this->createStub(AdminUrlProvider::class);
        $adminUrlProvider
            ->method('getAdminUrl')
            ->willReturn('admin');

        $domainMock = $this->createMock(Domain::class);
        $domainMock
            ->expects($this->any())->method('getDomainConfigById')
            ->with(Domain::FIRST_DOMAIN_ID)
            ->willReturn($this->createFirstDomainConfigWithPostfix());

        $contextResolverMock = $this->createMock(ContextResolverInterface::class);
        $contextResolverMock
            ->expects($this->any())->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(true);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domainMock, $contextResolverMock);

        $request = $this->createStub(Request::class);
        $request
            ->method('getUri')
            ->willReturn('http://other-domain.com/admin/users');
        $request
            ->method('getPathInfo')
            ->willReturn('/admin/users');
        $request
            ->method('getQueryString')
            ->willReturn(null);

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        // Should redirect to base URL without postfix - admin is always at base domain
        $this->assertEquals(DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/admin/users', $response->getTargetUrl());
    }

    public function testOnKernelRequestDoesNotRedirectWhenAlreadyOnCorrectUrl(): void
    {
        $adminUrlProvider = $this->createStub(AdminUrlProvider::class);
        $adminUrlProvider
            ->method('getAdminUrl')
            ->willReturn('admin');

        $domainMock = $this->createMock(Domain::class);
        $domainMock
            ->expects($this->any())->method('getDomainConfigById')
            ->with(Domain::FIRST_DOMAIN_ID)
            ->willReturn($this->createFirstDomainConfig());

        $contextResolverMock = $this->createMock(ContextResolverInterface::class);
        $contextResolverMock
            ->expects($this->any())->method('isCurrentContext')
            ->with(AdminContext::class)
            ->willReturn(true);

        $subscriber = new AdminDomainSubscriber($adminUrlProvider, $domainMock, $contextResolverMock);

        $request = $this->createStub(Request::class);
        $request
            ->method('getUri')
            ->willReturn(DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/admin/users');
        $request
            ->method('getPathInfo')
            ->willReturn('/admin/users');
        $request
            ->method('getQueryString')
            ->willReturn(null);

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->onKernelRequest($event);

        // Should not set any response since request is already on correct URL
        $this->assertNull($event->getResponse());
    }
}
