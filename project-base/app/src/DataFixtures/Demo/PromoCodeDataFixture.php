<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeCategoryFactory;
use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use App\Model\Order\PromoCode\PromoCodeLimitFactory;
use App\Model\Order\PromoCode\PromoCodeProductFactory;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PromoCodeDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const VALID_PROMO_CODE = 'valid_promo_code';
    public const PROMO_CODE_FOR_PRODUCT_ID_2 = 'promo_code_for_product_id_2';
    public const NOT_YET_VALID_PROMO_CODE = 'not_yet_valid_promo_code';
    public const NO_LONGER_VALID_PROMO_CODE = 'no_longer_valid_promo_code';
    public const PROMO_CODE_FOR_REGISTERED_ONLY = 'promo_code_for_registered_only';
    public const PROMO_CODE_FOR_VIP_PRICING_GROUP = 'promo_code_for_vip_pricing_group';
    public const PROMO_CODE_FOR_NEW_PRODUCT = 'promo_code_for_new_product';

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     * @param \App\Model\Order\PromoCode\PromoCodeProductFactory $promoCodeProductFactory
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryFactory $promoCodeCategoryFactory
     * @param \App\Model\Order\PromoCode\PromoCodeLimitFactory $promoCodeLimitFactory
     * @param \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory $promoCodeFlagFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly PromoCodeFacade $promoCodeFacade,
        private readonly PromoCodeDataFactoryInterface $promoCodeDataFactory,
        private readonly PromoCodeProductFactory $promoCodeProductFactory,
        private readonly PromoCodeCategoryFactory $promoCodeCategoryFactory,
        private readonly PromoCodeLimitFactory $promoCodeLimitFactory,
        private readonly PromoCodeFlagFactory $promoCodeFlagFactory,
        private readonly EntityManagerInterface $em,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
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
        $this->addReferenceForDomain(self::VALID_PROMO_CODE, $promoCode, Domain::FIRST_DOMAIN_ID);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-product2';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $this->promoCodeProductFactory->create($promoCode, $product);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_PRODUCT_ID_2, $promoCode, Domain::FIRST_DOMAIN_ID);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-not-yet-valid';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        $promoCodeData->datetimeValidFrom = new DateTime('+1 year');
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::NOT_YET_VALID_PROMO_CODE, $promoCode, Domain::FIRST_DOMAIN_ID);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-no-longer-valid';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        $promoCodeData->datetimeValidTo = new DateTime('-1 year');
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::NO_LONGER_VALID_PROMO_CODE, $promoCode, Domain::FIRST_DOMAIN_ID);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-for-registered-only';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        $promoCodeData->registeredCustomerUserOnly = true;
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_REGISTERED_ONLY, $promoCode, Domain::FIRST_DOMAIN_ID);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-for-vip-pricing-group';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        $vipPricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_VIP, Domain::FIRST_DOMAIN_ID, PricingGroup::class);
        $promoCodeData->limitedPricingGroups = [$vipPricingGroup];
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_VIP_PRICING_GROUP, $promoCode, Domain::FIRST_DOMAIN_ID);

        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test-for-new-product';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        /** @var \App\Model\Product\Flag\Flag $flag */
        $flag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW);
        $promoCodeData->flags = [$this->promoCodeFlagFactory->create($flag, PromoCodeFlag::TYPE_INCLUSIVE)];
        $promoCode = $this->promoCodeFacade->create($promoCodeData);
        $this->setDefaultLimit($promoCode);
        $this->addReferenceForDomain(self::PROMO_CODE_FOR_NEW_PRODUCT, $promoCode, Domain::FIRST_DOMAIN_ID);

        $this->loadForOtherDomains();
    }

    private function loadForOtherDomains(): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            if ($domainId === Domain::FIRST_DOMAIN_ID) {
                continue;
            }
            $promoCodeData = $this->promoCodeDataFactory->create();
            $promoCodeData->code = 'test';
            $promoCodeData->domainId = $domainId;
            $promoCodeData->identifier = 'test' . $domainId;
            $promoCode = $this->promoCodeFacade->create($promoCodeData);
            $this->setDefaultLimit($promoCode);
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
}
