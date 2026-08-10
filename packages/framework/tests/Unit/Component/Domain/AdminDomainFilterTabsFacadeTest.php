<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class AdminDomainFilterTabsFacadeTest extends TestCase
{
    private const string NAMESPACE = 'product-reviews';

    public function testSelectedDomainIdIsReturned(): void
    {
        $adminDomainFilterTabsFacade = $this->createAdminDomainFilterTabsFacade($session);
        $adminDomainFilterTabsFacade->setSelectedDomainId(self::NAMESPACE, 2);

        $this->assertSame(2, $adminDomainFilterTabsFacade->getSelectedDomainId(self::NAMESPACE));
    }

    public function testSelectedDomainIdWithinAllowedDomainIdsIsReturned(): void
    {
        $adminDomainFilterTabsFacade = $this->createAdminDomainFilterTabsFacade($session);
        $adminDomainFilterTabsFacade->setSelectedDomainId(self::NAMESPACE, 2);

        $this->assertSame(2, $adminDomainFilterTabsFacade->getSelectedDomainId(self::NAMESPACE, [1, 2]));
    }

    public function testSelectedDomainIdOutsideAllowedDomainIdsIsCleared(): void
    {
        $adminDomainFilterTabsFacade = $this->createAdminDomainFilterTabsFacade($session);
        $adminDomainFilterTabsFacade->setSelectedDomainId(self::NAMESPACE, 2);

        $this->assertNull($adminDomainFilterTabsFacade->getSelectedDomainId(self::NAMESPACE, [1, 3]));
        $this->assertNull($session->get('admin_domain_filter_tabs_' . self::NAMESPACE));
    }

    public function testSelectedDomainConfigOutsideAllowedDomainIdsIsCleared(): void
    {
        $adminDomainFilterTabsFacade = $this->createAdminDomainFilterTabsFacade($session);
        $adminDomainFilterTabsFacade->setSelectedDomainId(self::NAMESPACE, 2);

        $this->assertNull($adminDomainFilterTabsFacade->getSelectedDomainConfig(self::NAMESPACE, [1, 3]));
        $this->assertNull($session->get('admin_domain_filter_tabs_' . self::NAMESPACE));
    }

    public function testSelectedDomainIdOutsideAdminEnabledDomainIdsIsCleared(): void
    {
        $adminDomainFilterTabsFacade = $this->createAdminDomainFilterTabsFacade($session);
        $adminDomainFilterTabsFacade->setSelectedDomainId(self::NAMESPACE, 4);

        $this->assertNull($adminDomainFilterTabsFacade->getSelectedDomainId(self::NAMESPACE));
        $this->assertNull($session->get('admin_domain_filter_tabs_' . self::NAMESPACE));
    }

    /**
     * @param-out \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    private function createAdminDomainFilterTabsFacade(?SessionInterface &$session): AdminDomainFilterTabsFacade
    {
        $session = new Session(new MockArraySessionStorage());
        $request = new Request();
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn([1, 2, 3]);
        $domainStub->method('getDomainConfigById')->willReturnCallback(
            static fn (int $domainId): DomainConfig => new DomainConfig(
                $domainId,
                'http://example.com',
                'Domain ' . $domainId,
                'en',
                new DateTimeZone('UTC'),
                'http://example.com',
            ),
        );

        return new AdminDomainFilterTabsFacade($requestStack, $domainStub);
    }
}
