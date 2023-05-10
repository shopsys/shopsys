<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class PricingGroupDataFixture extends AbstractReferenceFixture
{
    public const PRICING_GROUP_ORDINARY = 'pricing_group_ordinary';
    public const PRICING_GROUP_VIP = 'pricing_group_vip';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactory $pricingGroupDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        private readonly PricingGroupFacade $pricingGroupFacade,
        private readonly PricingGroupDataFactoryInterface $pricingGroupDataFactory,
        private readonly Domain $domain,
        private readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->editDefaultPricingGroupOnDomain($domainConfig);
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function editDefaultPricingGroupOnDomain(DomainConfig $domainConfig): void
    {
        $defaultPricingGroupOnDomain = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId(),
        );
        $pricingGroupData = $this->pricingGroupDataFactory->createFromPricingGroup($defaultPricingGroupOnDomain);
        $pricingGroupData->name = t('Ordinary customer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
        $this->pricingGroupFacade->edit($defaultPricingGroupOnDomain->getId(), $pricingGroupData);
        $this->addReferenceForDomain(self::PRICING_GROUP_ORDINARY, $defaultPricingGroupOnDomain, $domainConfig->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function createVipPricingGroup(DomainConfig $domainConfig): void
    {
        $pricingGroupData = $this->pricingGroupDataFactory->create();
        $pricingGroupData->name = t('VIP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
        $domainId = $domainConfig->getId();
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        $this->addReferenceForDomain(self::PRICING_GROUP_VIP, $pricingGroup, $domainId);
    }
}
