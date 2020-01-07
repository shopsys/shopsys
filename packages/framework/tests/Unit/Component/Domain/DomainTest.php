<?php

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpFoundation\Request;

class DomainTest extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createDomainConfigFirst(): DomainConfig
    {
        return new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com:8080', 'example.com', 'cs');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createDomainConfigSecond(): DomainConfig
    {
        return new DomainConfig(Domain::SECOND_DOMAIN_ID, 'http://example.org:8080', 'example.org', 'en');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private function getDomainConfigs(): array
    {
        return [
            $this->createDomainConfigFirst(),
            $this->createDomainConfigSecond(),
        ];
    }

    public function testGetIdNotSet(): void
    {
        $settingMock = $this->createMock(Setting::class);

        $domain = new Domain($this->getDomainConfigs(), $settingMock);
        $this->expectException(\Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException::class);
        $domain->getId();
    }

    public function testSwitchDomainByRequest(): void
    {
        $settingMock = $this->createMock(Setting::class);

        $domain = new Domain($this->getDomainConfigs(), $settingMock);

        $requestMock = $this->getMockBuilder(Request::class)
            ->setMethods(['getSchemeAndHttpHost'])
            ->getMock();
        $requestMock
            ->expects($this->atLeastOnce())
            ->method('getSchemeAndHttpHost')
            ->willReturn('http://example.com:8080');

        $domain->switchDomainByRequest($requestMock);
        $this->assertSame(1, $domain->getId());
        $this->assertSame('example.com', $domain->getName());
        $this->assertSame('cs', $domain->getLocale());
    }

    public function testGetAllIncludingDomainConfigsWithoutDataCreated(): void
    {
        $domainConfigs = $this->getDomainConfigs();
        $settingMock = $this->createMock(Setting::class);

        $domain = new Domain($domainConfigs, $settingMock);

        $this->assertSame($domainConfigs, $domain->getAllIncludingDomainConfigsWithoutDataCreated());
    }

    public function testGetAll(): void
    {
        $domainConfigWithDataCreated = $this->createDomainConfigFirst();
        $domainConfigWithoutDataCreated = $this->createDomainConfigSecond();
        $domainConfigs = [
            $domainConfigWithDataCreated,
            $domainConfigWithoutDataCreated,
        ];
        $settingMock = $this->createMock(Setting::class);
        $settingMock
            ->expects($this->exactly(count($domainConfigs)))
            ->method('getForDomain')
            ->willReturnCallback(function ($key, $domainId) use ($domainConfigWithDataCreated) {
                $this->assertEquals(Setting::DOMAIN_DATA_CREATED, $key);
                if ($domainId === $domainConfigWithDataCreated->getId()) {
                    return true;
                }
                throw new \Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException();
            });

        $domain = new Domain($domainConfigs, $settingMock);

        $this->assertSame([$domainConfigWithDataCreated], $domain->getAll());
    }

    public function testGetDomainConfigById(): void
    {
        $domainConfigs = $this->getDomainConfigs();
        $settingMock = $this->createMock(Setting::class);

        $domain = new Domain($domainConfigs, $settingMock);

        $this->assertSame($domainConfigs[0], $domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));
        $this->assertSame($domainConfigs[1], $domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID));

        $this->expectException(\Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException::class);
        $domain->getDomainConfigById(Domain::THIRD_DOMAIN_ID);
    }

    public function testGetAllLocales(): void
    {
        $domainConfigs = [
            $this->createDomainConfigFirst(),
            $this->createDomainConfigSecond(),
            new DomainConfig(Domain::THIRD_DOMAIN_ID, 'http://example.cz:8080', 'example.cz', 'cs'),
        ];
        $settingMock = $this->createMock(Setting::class);

        $domain = new Domain($domainConfigs, $settingMock);

        $expectedLocales = [
            'cs' => 'cs',
            'en' => 'en',
        ];
        $this->assertSame($expectedLocales, $domain->getAllLocales());
    }
}
