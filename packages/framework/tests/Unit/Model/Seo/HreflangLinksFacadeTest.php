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

    private const array SLUGS_INDEXED_BY_DOMAIN_ID_AND_FLAG_ID = [
        self::CURRENT_DOMAIN_ID => [
            1 => 'new',
            2 => 'sale',
        ],
        self::ALTERNATIVE_DOMAIN_ID => [
            1 => 'novinka',
            2 => 'vyprodej',
        ],
    ];

    private const array DOMAIN_URLS_INDEXED_BY_DOMAIN_ID = [
        self::CURRENT_DOMAIN_ID => 'https://en.example.com',
        self::ALTERNATIVE_DOMAIN_ID => 'https://cs.example.com',
    ];

    public function testHreflangLinksOfFlagsOnAllRelevantDomainsAreInOrderOfFlags(): void
    {
        $hreflangLinksFacade = $this->createHreflangLinksFacade();

        $hreflangLinks = $hreflangLinksFacade->getForFlags(
            [$this->createFlagStub(2), $this->createFlagStub(1)],
            self::CURRENT_DOMAIN_ID,
        );

        $this->assertEquals([
            [
                new HreflangLink('en', 'https://en.example.com/sale', self::CURRENT_DOMAIN_ID),
                new HreflangLink('cs', 'https://cs.example.com/vyprodej', self::ALTERNATIVE_DOMAIN_ID),
            ],
            [
                new HreflangLink('en', 'https://en.example.com/new', self::CURRENT_DOMAIN_ID),
                new HreflangLink('cs', 'https://cs.example.com/novinka', self::ALTERNATIVE_DOMAIN_ID),
            ],
        ], $hreflangLinks);
    }

    public function testHreflangLinksOfFlagsWithoutAbsoluteUrlContainSlugsOnly(): void
    {
        $hreflangLinksFacade = $this->createHreflangLinksFacade();

        $hreflangLinks = $hreflangLinksFacade->getForFlags([$this->createFlagStub(1)], self::CURRENT_DOMAIN_ID, false);

        $this->assertEquals([
            [
                new HreflangLink('en', 'new', self::CURRENT_DOMAIN_ID),
                new HreflangLink('cs', 'novinka', self::ALTERNATIVE_DOMAIN_ID),
            ],
        ], $hreflangLinks);
    }

    private function createHreflangLinksFacade(): HreflangLinksFacade
    {
        $seoSettingFacade = $this->createStub(SeoSettingFacade::class);
        $seoSettingFacade->method('getAlternativeDomainsForDomain')->willReturn([self::ALTERNATIVE_DOMAIN_ID]);

        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturnMap([
            [self::CURRENT_DOMAIN_ID, $this->createDomainConfigStub('en')],
            [self::ALTERNATIVE_DOMAIN_ID, $this->createDomainConfigStub('cs')],
        ]);

        $friendlyUrlFacade = $this->createStub(FriendlyUrlFacade::class);
        $friendlyUrlFacade->method('getMainFriendlyUrlSlugsIndexedByEntityId')->willReturnCallback(
            static fn (int $domainId, string $routeName, array $entityIds): array => array_intersect_key(
                self::SLUGS_INDEXED_BY_DOMAIN_ID_AND_FLAG_ID[$domainId],
                array_flip($entityIds),
            ),
        );
        $friendlyUrlFacade->method('getAbsoluteUrlByMainFriendlyUrlSlug')->willReturnCallback(
            static fn (int $domainId, string $slug): string => self::DOMAIN_URLS_INDEXED_BY_DOMAIN_ID[$domainId] . '/' . $slug,
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
