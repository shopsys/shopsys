<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class PricingGroupDataFixture extends AbstractReferenceFixture
{
    public const string PRICING_GROUP_ORDINARY = 'pricing_group_ordinary';
    public const string PRICING_GROUP_VIP = 'pricing_group_vip';

    public function __construct(
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly PricingGroupDataFactory $pricingGroupDataFactory,
        private readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->editDefaultPricingGroupOnDomain($domainConfig);
        }

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $this->createVipPricingGroup($domainConfig);
        }
    }

    /**
     * The default pricing group for domain 1 is created in database migration.
     *
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135346
     *
     * The default pricing groups for the other domains are created during build (in "domains-data-create" phing target).
     * @see \Shopsys\FrameworkBundle\Component\Domain\DomainDataCreator
     */
    private function editDefaultPricingGroupOnDomain(DomainConfig $domainConfig): void
    {
        $defaultPricingGroupOnDomain = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(
            $domainConfig->getId(),
        );
        $pricingGroupData = $this->pricingGroupDataFactory->createFromPricingGroup($defaultPricingGroupOnDomain);
        $pricingGroupData->name = t('Ordinary customer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
        $this->pricingGroupFacade->edit($defaultPricingGroupOnDomain->getId(), $pricingGroupData);
        $this->addReferenceForDomain(self::PRICING_GROUP_ORDINARY, $defaultPricingGroupOnDomain, $domainConfig->getId());
    }

    private function createVipPricingGroup(DomainConfig $domainConfig): void
    {
        $pricingGroupData = $this->pricingGroupDataFactory->create();
        $pricingGroupData->name = t('VIP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
        $domainId = $domainConfig->getId();
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        $this->addReferenceForDomain(self::PRICING_GROUP_VIP, $pricingGroup, $domainId);
    }
}
