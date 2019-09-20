<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class PricingGroupDataFixture extends AbstractReferenceFixture
{
    public const PRICING_GROUP_ORDINARY = 'pricing_group_ordinary';
    public const PRICING_GROUP_PARTNER = 'pricing_group_partner';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    protected $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface
     */
    protected $pricingGroupDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupDataFactoryInterface $pricingGroupDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        PricingGroupFacade $pricingGroupFacade,
        PricingGroupDataFactoryInterface $pricingGroupDataFactory,
        Domain $domain,
        PricingGroupSettingFacade $pricingGroupSettingFacade
    ) {
        $this->pricingGroupFacade = $pricingGroupFacade;
        $this->pricingGroupDataFactory = $pricingGroupDataFactory;
        $this->domain = $domain;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $pricingGroupData = $this->pricingGroupDataFactory->create();

            $this->editDefaultPricingGroupOnDomain($domainConfig);

            if ($domainId !== 2) {
                $pricingGroupData->name = t('Partner', [], 'dataFixtures', $locale);
                $this->createPricingGroup($pricingGroupData, $domainId, self::PRICING_GROUP_PARTNER);
            }

            $pricingGroupData->name = t('VIP customer', [], 'dataFixtures', $locale);
            $this->createPricingGroup($pricingGroupData, $domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
     * @param int $domainId
     * @param string|null $referenceName
     */
    protected function createPricingGroup(
        PricingGroupData $pricingGroupData,
        int $domainId,
        ?string $referenceName = null
    ): void {
        $pricingGroup = $this->pricingGroupFacade->create($pricingGroupData, $domainId);
        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $pricingGroup, $domainId);
        }
    }

    /**
     * The default pricing group for domain 1 is created in database migration.
     * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135346
     *
     * The default pricing groups for the other domains are created during build (in "domains-data-create" phing target).
     * @see \Shopsys\FrameworkBundle\Component\Domain\DomainDataCreator
     *
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    protected function editDefaultPricingGroupOnDomain(DomainConfig $domainConfig): void
    {
        $defaultPricingGroupOnDomain = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainConfig->getId());
        $pricingGroupData = $this->pricingGroupDataFactory->createFromPricingGroup($defaultPricingGroupOnDomain);
        $pricingGroupData->name = t('Ordinary customer', [], 'dataFixtures', $domainConfig->getLocale());
        $this->pricingGroupFacade->edit($defaultPricingGroupOnDomain->getId(), $pricingGroupData);
        $this->addReferenceForDomain(self::PRICING_GROUP_ORDINARY, $defaultPricingGroupOnDomain, $domainConfig->getId());
    }
}
