<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\HttpFoundation\Request;

class DomainTest extends TestCase
{
    private const int TEST_DOMAIN_ID_4 = 4;
    private const int TEST_DOMAIN_ID_5 = 5;
    private const int TEST_DOMAIN_ID_6 = 6;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private static function createDomainConfigFirst(): DomainConfig
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return new DomainConfig(
            Domain::FIRST_DOMAIN_ID,
            'http://example.com:8080',
            'example.com',
            'cs',
            $defaultTimeZone,
            'http://example.com:8080',
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private static function createDomainConfigSecond(): DomainConfig
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return new DomainConfig(
            Domain::SECOND_DOMAIN_ID,
            'http://example.org:8080',
            'example.org',
            'en',
            $defaultTimeZone,
            'http://example.org:8080',
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private static function createDomainConfigWithPostfix(): DomainConfig
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return new DomainConfig(
            Domain::THIRD_DOMAIN_ID,
            'http://example.com:8080/sk',
            'example.com SK',
            'sk',
            $defaultTimeZone,
            'http://example.com:8080',
            DomainConfig::TYPE_B2C,
            true,
            '/sk',
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private static function getDomainConfigs(): array
    {
        return [
            self::createDomainConfigFirst(),
            self::createDomainConfigSecond(),
            self::createDomainConfigWithPostfix(),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private static function getDomainConfigsWithRootLast(): array
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');

        return [
            new DomainConfig(
                self::TEST_DOMAIN_ID_4,
                'http://example.com:8080/sk',
                'SK Domain',
                'sk',
                $defaultTimeZone,
                'http://example.com:8080',
                DomainConfig::TYPE_B2C,
                true,
                '/sk',
            ),
            new DomainConfig(
                self::TEST_DOMAIN_ID_5,
                'http://example.com:8080/sk_SK',
                'SK2 Domain',
                'sk',
                $defaultTimeZone,
                'http://example.com:8080',
                DomainConfig::TYPE_B2C,
                true,
                '/sk_SK',
            ),
            new DomainConfig(
                self::TEST_DOMAIN_ID_6,
                'http://example.com:8080',
                'Root Domain',
                'en',
                $defaultTimeZone,
                'http://example.com:8080',
            ),
        ];
    }

    public function testGetIdNotSet(): void
    {
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            self::getDomainConfigs(),
            $settingMock,
            $administratorFacadeMock,
        );

        $this->expectException(NoDomainSelectedException::class);
        $domain->getId();
    }

    /**
     * @dataProvider switchDomainByRequestDataProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @param string $requestHost
     * @param string $requestPath
     * @param int $expectedDomainId
     * @param string $expectedLocale
     * @param string $description
     */
    public function testSwitchDomainByRequest(
        array $domainConfigs,
        string $requestHost,
        string $requestPath,
        int $expectedDomainId,
        string $expectedLocale,
        string $description = '',
    ): void {
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $administratorFacadeMock,
        );

        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getSchemeAndHttpHost')
            ->willReturn($requestHost);
        $requestMock
            ->method('getPathInfo')
            ->willReturn($requestPath);

        $domain->switchDomainByRequest($requestMock);

        $this->assertSame($expectedDomainId, $domain->getId(), $description);
        $this->assertSame($expectedLocale, $domain->getLocale(), $description);
    }

    /**
     * @return array<string, array{domainConfigs: array, requestHost: string, requestPath: string, expectedDomainId: int, expectedLocale: string, description: string}>
     */
    public static function switchDomainByRequestDataProvider(): array
    {
        $domainConfigs = self::getDomainConfigs();

        $domainConfigsWhereRootDomainIsDefinedAsLast = self::getDomainConfigsWithRootLast();

        return [
            'base domain without postfix - root path' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/',
                'expectedDomainId' => Domain::FIRST_DOMAIN_ID,
                'expectedLocale' => 'cs',
                'description' => 'Root path should match base domain',
            ],
            'base domain without postfix - any path' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/products',
                'expectedDomainId' => Domain::FIRST_DOMAIN_ID,
                'expectedLocale' => 'cs',
                'description' => 'Any path should match base domain when no specific postfix matches',
            ],
            'base domain without postfix - path starting with another domain postfix' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/skkk',
                'expectedDomainId' => Domain::FIRST_DOMAIN_ID,
                'expectedLocale' => 'cs',
                'description' => 'Path that only partially matches postfix should go to base domain',
            ],
            'base domain with postfix - exact postfix match' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk',
                'expectedDomainId' => Domain::THIRD_DOMAIN_ID,
                'expectedLocale' => 'sk',
                'description' => 'Exact postfix match should work',
            ],
            'base domain with postfix - postfix with trailing path' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk/',
                'expectedDomainId' => Domain::THIRD_DOMAIN_ID,
                'expectedLocale' => 'sk',
                'description' => 'Postfix with trailing slash should match',
            ],
            'base domain with postfix - subpath under postfix' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk/products',
                'expectedDomainId' => Domain::THIRD_DOMAIN_ID,
                'expectedLocale' => 'sk',
                'description' => 'Subpaths under postfix should match domain with postfix',
            ],
            'base domain with postfix - subpath with categories' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk/categories/electronics',
                'expectedDomainId' => Domain::THIRD_DOMAIN_ID,
                'expectedLocale' => 'sk',
                'description' => 'Deep subpaths under postfix should match domain with postfix',
            ],
            'different host matches second domain' => [
                'domainConfigs' => $domainConfigs,
                'requestHost' => 'http://example.org:8080',
                'requestPath' => '/any/path',
                'expectedDomainId' => Domain::SECOND_DOMAIN_ID,
                'expectedLocale' => 'en',
                'description' => 'Different host should match correct domain',
            ],
            'overlapping postfixes - /sk_SK wins over /sk' => [
                'domainConfigs' => $domainConfigsWhereRootDomainIsDefinedAsLast,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk_SK/products',
                'expectedDomainId' => self::TEST_DOMAIN_ID_5,
                'expectedLocale' => 'sk',
                'description' => '/sk_SK/products should match /sk_SK domain, not /sk domain (longest match)',
            ],
            'root domain last - specific /sk wins over root' => [
                'domainConfigs' => $domainConfigsWhereRootDomainIsDefinedAsLast,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk',
                'expectedDomainId' => self::TEST_DOMAIN_ID_4,
                'expectedLocale' => 'sk',
                'description' => '/sk should match /sk domain, not root (even though root comes last in the confiuration)',
            ],
            'root domain last - specific /sk_SK wins over root' => [
                'domainConfigs' => $domainConfigsWhereRootDomainIsDefinedAsLast,
                'requestHost' => 'http://example.com:8080',
                'requestPath' => '/sk_SK/products',
                'expectedDomainId' => self::TEST_DOMAIN_ID_5,
                'expectedLocale' => 'sk',
                'description' => '/sk_SK/products should match /sk_SK domain, not root',
            ],
        ];
    }

    public function testGetAllIncludingDomainConfigsWithoutDataCreated(): void
    {
        $domainConfigs = self::getDomainConfigs();
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $administratorFacadeMock,
        );

        $this->assertSame($domainConfigs, $domain->getAllIncludingDomainConfigsWithoutDataCreated());
    }

    public function testGetAll(): void
    {
        $domainConfigWithDataCreated = self::createDomainConfigFirst();
        $domainConfigWithoutDataCreated = self::createDomainConfigSecond();
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

                throw new SettingValueNotFoundException();
            });

        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $administratorFacadeMock,
        );

        $this->assertSame([1 => $domainConfigWithDataCreated], $domain->getAll());
    }

    public function testGetDomainConfigById(): void
    {
        $domainConfigs = self::getDomainConfigs();
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $administratorFacadeMock,
        );

        $this->assertSame($domainConfigs[0], $domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));
        $this->assertSame($domainConfigs[1], $domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID));
        $this->assertSame($domainConfigs[2], $domain->getDomainConfigById(Domain::THIRD_DOMAIN_ID));

        $this->expectException(InvalidDomainIdException::class);
        $domain->getDomainConfigById(4);
    }

    public function testGetAllLocales(): void
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfigs = [
            self::createDomainConfigFirst(),
            self::createDomainConfigSecond(),
            new DomainConfig(
                Domain::THIRD_DOMAIN_ID,
                'http://example.cz:8080',
                'example.cz',
                'cs',
                $defaultTimeZone,
                'http://example.cz:8080',
            ),
        ];
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            $domainConfigs,
            $settingMock,
            $administratorFacadeMock,
        );

        $expectedLocales = [
            'cs' => 'cs',
            'en' => 'en',
        ];
        $this->assertSame($expectedLocales, $domain->getAllLocales());
    }

    public function testSwitchDomainByRequestThrowsExceptionWhenNoDomainMatches(): void
    {
        $settingMock = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            self::getDomainConfigs(),
            $settingMock,
            $administratorFacadeMock,
        );

        $requestMock = $this->createMock(Request::class);
        $requestMock
            ->method('getSchemeAndHttpHost')
            ->willReturn('http://unknown-host.com:8080');
        $requestMock
            ->method('getPathInfo')
            ->willReturn('/any/path');

        $this->expectException(UnableToResolveDomainException::class);
        $domain->switchDomainByRequest($requestMock);
    }
}
