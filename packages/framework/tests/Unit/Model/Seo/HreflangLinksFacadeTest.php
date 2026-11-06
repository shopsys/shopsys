<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Seo;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLink;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class HreflangLinksFacadeTest extends TestCase
{
    private const int CURRENT_DOMAIN_ID = 1;

    private const int ALTERNATIVE_DOMAIN_ID = 2;

    private const array LOCALES_INDEXED_BY_DOMAIN_ID = [
        self::CURRENT_DOMAIN_ID => 'en',
        self::ALTERNATIVE_DOMAIN_ID => 'cs',
    ];

    public function testHreflangLinksOfFlagsAreOnAllRelevantDomains(): void
    {
        $hreflangLinks = $this->createHreflangLinksFacade()->getForFlags([$this->createFlagStub(1)], self::CURRENT_DOMAIN_ID);

        $this->assertEquals([
            [
                new HreflangLink('en', 'https://en.example.com/entity-1-en', self::CURRENT_DOMAIN_ID),
                new HreflangLink('cs', 'https://cs.example.com/entity-1-cs', self::ALTERNATIVE_DOMAIN_ID),
            ],
        ], $hreflangLinks);
    }

    public function testHreflangLinksWithoutAbsoluteUrlContainSlugsOnly(): void
    {
        $hreflangLinks = $this->createHreflangLinksFacade()->getForFlags([$this->createFlagStub(1)], self::CURRENT_DOMAIN_ID, false);

        $this->assertEquals([
            [
                new HreflangLink('en', 'entity-1-en', self::CURRENT_DOMAIN_ID),
                new HreflangLink('cs', 'entity-1-cs', self::ALTERNATIVE_DOMAIN_ID),
            ],
        ], $hreflangLinks);
    }

    private function createHreflangLinksFacade(): HreflangLinksFacade
    {
        $seoSettingFacade = $this->createStub(SeoSettingFacade::class);
        $seoSettingFacade->method('getAlternativeDomainsForDomain')->willReturn([self::ALTERNATIVE_DOMAIN_ID]);

        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturnCallback(
            fn (int $domainId): DomainConfig => $this->createDomainConfigStub(self::LOCALES_INDEXED_BY_DOMAIN_ID[$domainId]),
        );

        $friendlyUrlFacade = $this->createStub(FriendlyUrlFacade::class);
        $friendlyUrlFacade->method('getMainFriendlyUrlSlugsIndexedByEntityId')->willReturnCallback(
            static fn (int $domainId, string $routeName, array $entityIds): array => array_combine(
                $entityIds,
                array_map(
                    static fn (int $entityId): string => sprintf('entity-%d-%s', $entityId, self::LOCALES_INDEXED_BY_DOMAIN_ID[$domainId]),
                    $entityIds,
                ),
            ),
        );
        $friendlyUrlFacade->method('getAbsoluteUrlByMainFriendlyUrlSlug')->willReturnCallback(
            static fn (int $domainId, string $slug): string => sprintf('https://%s.example.com/%s', self::LOCALES_INDEXED_BY_DOMAIN_ID[$domainId], $slug),
        );

        return new HreflangLinksFacade(
            $seoSettingFacade,
            $domain,
            $friendlyUrlFacade,
            $this->createStub(ProductVisibilityFacade::class),
            $this->createStub(PricingGroupSettingFacade::class),
        );
    }

    private function createFlagStub(int $id): Flag
    {
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn($id);

        return $flag;
    }

    private function createDomainConfigStub(string $locale): DomainConfig
    {
        $domainConfig = $this->createStub(DomainConfig::class);
        $domainConfig->method('getLocale')->willReturn($locale);

        return $domainConfig;
    }
}
