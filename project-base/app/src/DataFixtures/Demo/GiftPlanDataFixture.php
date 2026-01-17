<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanData;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanDataFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\Clock\DatePoint;

class GiftPlanDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string GIFT_PLAN_PREFIX = 'gift_plan_prefix_';
    public const string GIFT_PLAN_VALID = 'gift_plan_valid';
    public const string GIFT_PLAN_INVALID = 'gift_plan_invalid';
    public const string GIFT_PLAN_SALE_EXCLUDED = 'gift_plan_sale_excluded';

    private const string UUID_NAMESPACE = '0338e624-c961-4475-a29d-c90080d02d1f';

    public function __construct(
        private readonly GiftPlanDataFactory $giftPlanDataFactory,
        private readonly GiftPlanFacade $giftPlanFacade,
        private readonly GiftPlanSettingFacade $giftPlanSettingFacade,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager)
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);

            $convertedPrice = $this->priceConverter->convertPriceToInputPriceInDomainDefaultCurrency(
                Money::create(24),
                $currencyCzk,
                $vat->getPercent(),
                $domainId,
            );

            $this->giftPlanSettingFacade->setInputGiftPrice($convertedPrice, $domainId);

            $giftPlanData = $this->giftPlanDataFactory->create();
            $giftPlanData->name = 'Gift plan valid';
            $giftPlanData->domainId = $domainId;
            $giftPlanData->validFrom = null;
            $giftPlanData->validTo = (new DatePoint())->modify('+30 days');
            $giftPlanData->giftProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');
            $giftPlanData->mainProducts = [$this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14')];
            $giftPlan = $this->createGiftPlan($giftPlanData);
            $this->addReferenceForDomain(self::GIFT_PLAN_VALID, $giftPlan, $domainId);


            $giftPlanData = $this->giftPlanDataFactory->create();
            $giftPlanData->name = 'Gift plan invalid';
            $giftPlanData->domainId = $domainId;
            $giftPlanData->validFrom = null;
            $giftPlanData->validTo = (new DatePoint())->modify('-3 days');
            $giftPlanData->giftProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');
            $giftPlanData->mainProducts = [$this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14')];
            $giftPlan = $this->createGiftPlan($giftPlanData);
            $this->addReferenceForDomain(self::GIFT_PLAN_INVALID, $giftPlan, $domainId);


            $giftPlanData = $this->giftPlanDataFactory->create();
            $giftPlanData->name = 'Gift plan with sale excluded product';
            $giftPlanData->domainId = $domainId;
            $giftPlanData->validFrom = null;
            $giftPlanData->validTo = null;
            $giftPlanData->giftProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '76');
            $giftPlanData->mainProducts = [$this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14')];
            $giftPlan = $this->createGiftPlan($giftPlanData);
            $this->addReferenceForDomain(self::GIFT_PLAN_SALE_EXCLUDED, $giftPlan, $domainId);
        }
    }

    private function createGiftPlan(GiftPlanData $giftPlanData): GiftPlan
    {
        $giftPlanData->uuid = Uuid::uuid5(
            self::UUID_NAMESPACE,
            implode('-', array_map(static fn (Product $mainProduct) => $mainProduct->getCatnum(), $giftPlanData->mainProducts)) . '-' .
            $giftPlanData->giftProduct->getCatnum() . '-' .
            $giftPlanData->name . '-' .
            $giftPlanData->domainId,
        )->toString();

        $giftPlan = $this->giftPlanFacade->create($giftPlanData);

        $this->addReferenceForDomain(self::GIFT_PLAN_PREFIX . $giftPlan->getId(), $giftPlan, $giftPlanData->domainId);

        return $giftPlan;
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
