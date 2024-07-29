<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\PromoCode\PromoCode;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PromoCodeDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string VALID_PROMO_CODE = 'valid_promo_code';
    public const string PROMO_CODE_FOR_PRODUCT_ID_2 = 'promo_code_for_product_id_2';
    public const string NOT_YET_VALID_PROMO_CODE = 'not_yet_valid_promo_code';
    public const string NO_LONGER_VALID_PROMO_CODE = 'no_longer_valid_promo_code';
    public const string PROMO_CODE_FOR_REGISTERED_ONLY = 'promo_code_for_registered_only';
    public const string PROMO_CODE_FOR_VIP_PRICING_GROUP = 'promo_code_for_vip_pricing_group';
    public const string PROMO_CODE_FOR_NEW_PRODUCT = 'promo_code_for_new_product';

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory $promoCodeProductFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory $promoCodeCategoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory $promoCodeLimitFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory $promoCodeFlagFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private readonly PromoCodeFacade $promoCodeFacade,
        private readonly PromoCodeDataFactoryInterface $promoCodeDataFactory,
        private readonly PromoCodeProductFactory $promoCodeProductFactory,
        private readonly PromoCodeCategoryFactory $promoCodeCategoryFactory,
        private readonly PromoCodeLimitFactory $promoCodeLimitFactory,
        private readonly PromoCodeFlagFactory $promoCodeFlagFactory,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $domainId = $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig()->getId();

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test';
        $promoCodeData->domainId = $domainId;
        $promoCode = $this->promoCodeFacade->create($promoCodeData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->promoCodeProductFactory->create($promoCode, $product);

        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_FOOD);
        $this->promoCodeCategoryFactory->create($promoCode, $category);

        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS);
        $this->promoCodeCategoryFactory->create($promoCode, $category);

        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::VALID_PROMO_CODE, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-product2';
        $promoCodeData->domainId = $domainId;
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $this->promoCodeProductFactory->create($promoCode, $product);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_PRODUCT_ID_2, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-not-yet-valid';
        $promoCodeData->domainId = $domainId;
        $promoCodeData->datetimeValidFrom = new DateTime('+1 year');
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::NOT_YET_VALID_PROMO_CODE, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-no-longer-valid';
        $promoCodeData->domainId = $domainId;
        $promoCodeData->datetimeValidTo = new DateTime('-1 year');
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::NO_LONGER_VALID_PROMO_CODE, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-for-registered-only';
        $promoCodeData->domainId = $domainId;
        $promoCodeData->registeredCustomerUserOnly = true;
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_REGISTERED_ONLY, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-for-vip-pricing-group';
        $promoCodeData->domainId = $domainId;
        $vipPricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_VIP, $domainId, PricingGroup::class);
        $promoCodeData->limitedPricingGroups = [$vipPricingGroup];
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_VIP_PRICING_GROUP, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-for-new-product';
        $promoCodeData->domainId = $domainId;
        /** @var \App\Model\Product\Flag\Flag $flag */
        $flag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $promoCodeData->flags = [$this->promoCodeFlagFactory->create($flag, PromoCodeFlag::TYPE_INCLUSIVE)];
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_NEW_PRODUCT, $promoCode, $domainId);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test100';
        $promoCodeData->discountType = PromoCode::DISCOUNT_TYPE_NOMINAL;
        $promoCodeData->domainId = $domainId;
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultNominalLimit($promoCode);

        $this->loadForOtherDomains();
    }

    private function loadForOtherDomains(): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if ($domainConfig === $this->domainsForDataFixtureProvider->getFirstAllowedDomainConfig()) {
                continue;
            }

            $domainId = $domainConfig->getId();

            $promoCodeData = $this->promoCodeDataFactory->create();
            $promoCodeData->code = 'test';
            $promoCodeData->domainId = $domainId;
            $promoCode = $this->promoCodeFacade->create($promoCodeData);
            $this->setDefaultLimit($promoCode);

            $promoCodeData = $this->promoCodeDataFactory->create();
            $promoCodeData->code = 'test100';
            $promoCodeData->discountType = PromoCode::DISCOUNT_TYPE_NOMINAL;
            $promoCodeData->domainId = $domainId;
            $promoCode = $this->promoCodeFacade->create($promoCodeData);
            $this->setDefaultNominalLimit($promoCode);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            CategoryDataFixture::class,
            PricingGroupDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    private function setDefaultLimit(PromoCode $promoCode): void
    {
        $promoCodeLimit = $this->promoCodeLimitFactory->create('1.0', '10');
        $promoCodeLimit->setPromoCode($promoCode);
        $this->em->persist($promoCodeLimit);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    private function setDefaultNominalLimit(PromoCode $promoCode): void
    {
        $promoCodeLimit = $this->promoCodeLimitFactory->create('101', '100');
        $promoCodeLimit->setPromoCode($promoCode);
        $this->em->persist($promoCodeLimit);
        $this->em->flush();
    }
}
