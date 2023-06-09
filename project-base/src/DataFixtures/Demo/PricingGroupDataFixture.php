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
    public const PRICING_GROUP_PARTNER = 'pricing_group_partner';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface $pricingGroupDataFactory
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
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $pricingGroupData = $this->pricingGroupDataFactory->create();

            $this->editDefaultPricingGroupOnDomain($domainConfig);

            if ($domainId !== Domain::SECOND_DOMAIN_ID) {
                $pricingGroupData->name = t('Partner', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                $this->createPricingGroup($pricingGroupData, $domainId, self::PRICING_GROUP_PARTNER);
            }

            $pricingGroupData->name = t('VIP customer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $this->createPricingGroup($pricingGroupData, $domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @param int $domainId
     * @param string|null $referenceName
     */
    private function createPricingGroup(
        PricingGroupData $pricingGroupData,
        int $domainId,
        ?string $referenceName = null,
    ): void {
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $pricingGroup, $domainId);
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
        $defaultPricingGroupOnDomain = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(
            $domainConfig->getId(),
        );
        $pricingGroupData = $this->pricingGroupDataFactory->createFromPricingGroup($defaultPricingGroupOnDomain);
        $pricingGroupData->name = t('Ordinary customer', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
        $this->pricingGroupFacade->edit($defaultPricingGroupOnDomain->getId(), $pricingGroupData);
        $this->addReferenceForDomain(
            self::PRICING_GROUP_ORDINARY,
            $defaultPricingGroupOnDomain,
            $domainConfig->getId(),
        );
    }
}
