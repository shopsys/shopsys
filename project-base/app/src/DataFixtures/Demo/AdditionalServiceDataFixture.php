<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDataFactory;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\AdditionalService\ZboziServiceTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class AdditionalServiceDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '5d1f3a7e-9c4b-4e8a-b2d6-8f0c1a3e5b7d';

    public const string ADDITIONAL_SERVICE_ASSEMBLY = 'additional_service_assembly';
    public const string ADDITIONAL_SERVICE_EXTENDED_WARRANTY = 'additional_service_extended_warranty';
    public const string ADDITIONAL_SERVICE_GIFT_WRAPPING = 'additional_service_gift_wrapping';
    public const string ADDITIONAL_SERVICE_ENGRAVING = 'additional_service_engraving';
    public const string ADDITIONAL_SERVICE_APPLIANCE_REMOVAL = 'additional_service_appliance_removal';
    public const string ADDITIONAL_SERVICE_INSURANCE = 'additional_service_insurance';

    public function __construct(
        private readonly AdditionalServiceFacade $additionalServiceFacade,
        private readonly AdditionalServiceDataFactory $additionalServiceDataFactory,
        private readonly ProductFacade $productFacade,
        private readonly ProductDataFactory $productDataFactory,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $assembly = $this->createAdditionalService(
            self::ADDITIONAL_SERVICE_ASSEMBLY,
            'SERVICE-ASSEMBLY',
            static fn (string $locale): string => t('Professional assembly', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            Money::create(499),
            ZboziServiceTypeEnum::FREE_INSTALLATION,
            static fn (string $locale): string => t('The product will be assembled and installed by our certified technician.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            1,
        );

        $extendedWarranty = $this->createAdditionalService(
            self::ADDITIONAL_SERVICE_EXTENDED_WARRANTY,
            'SERVICE-WARRANTY',
            static fn (string $locale): string => t('Extended warranty for 5 years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            Money::create(1290),
            ZboziServiceTypeEnum::EXTENDED_WARRANTY,
            static fn (string $locale): string => t('The warranty of the product is extended to 5 years in total.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            0,
            false,
        );

        $giftWrapping = $this->createAdditionalService(
            self::ADDITIONAL_SERVICE_GIFT_WRAPPING,
            'SERVICE-GIFT-WRAP',
            static fn (string $locale): string => t('Gift wrapping', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            Money::create(99),
            ZboziServiceTypeEnum::GIFT_PACKAGE,
            null,
            0,
        );

        $engraving = $this->createAdditionalService(
            self::ADDITIONAL_SERVICE_ENGRAVING,
            'SERVICE-ENGRAVING',
            static fn (string $locale): string => t('Custom engraving', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            Money::create(349),
            ZboziServiceTypeEnum::CUSTOM,
            static fn (string $locale): string => t('We will engrave a text of your choice on the product.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            3,
        );

        $applianceRemoval = $this->createAdditionalService(
            self::ADDITIONAL_SERVICE_APPLIANCE_REMOVAL,
            'SERVICE-APPLIANCE-REMOVAL',
            static fn (string $locale): string => t('Old appliance removal', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            Money::create(199),
            ZboziServiceTypeEnum::APPLIANCE_PICKUP,
            static fn (string $locale): string => t('We will collect and ecologically dispose of your old appliance.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            0,
        );

        $insurance = $this->createAdditionalService(
            self::ADDITIONAL_SERVICE_INSURANCE,
            'SERVICE-INSURANCE',
            static fn (string $locale): string => t('Damage insurance for 2 years', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            Money::create(890),
            ZboziServiceTypeEnum::EXTENDED_WARRANTY,
            null,
            0,
            true,
            false,
            false,
        );

        $this->assignAdditionalServicesToProduct('1', [$assembly, $extendedWarranty, $giftWrapping, $engraving, $applianceRemoval, $insurance]);
        $this->assignAdditionalServicesToProduct('2', [$giftWrapping]);
        $this->assignAdditionalServicesToProduct('72', [$giftWrapping]);
        $this->assignAdditionalServicesToProduct('53', [$extendedWarranty, $giftWrapping]);
        $this->assignAdditionalServicesToProduct('54', [$extendedWarranty]);
    }

    /**
     * @param callable(string): string $translateName
     * @param callable(string): string|null $translateDescription
     */
    private function createAdditionalService(
        string $referenceName,
        string $catnum,
        callable $translateName,
        Money $priceCzk,
        string $zboziServiceType,
        ?callable $translateDescription,
        int $deliveryDaysExtension,
        bool $useProductVatRate = true,
        bool $showInFeeds = true,
        bool $enabled = true,
    ): AdditionalService {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = $catnum;
        $additionalServiceData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $catnum)->toString();
        $additionalServiceData->zboziServiceType = $zboziServiceType;
        $additionalServiceData->deliveryDaysExtension = $deliveryDaysExtension;

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $additionalServiceData->name[$locale] = $translateName($locale);
            $additionalServiceData->feedName[$locale] = $translateName($locale);
            $additionalServiceData->zboziDescription[$locale] = $translateName($locale);
            $additionalServiceData->description[$locale] = $translateDescription === null ? null : $translateDescription($locale);
        }

        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $domainId, Vat::class);

            $additionalServiceData->pricesIndexedByDomainId[$domainId] = $this->priceConverter->convertPriceToInputPriceInDomainDefaultCurrency(
                $priceCzk,
                $currencyCzk,
                $vat->getPercent(),
                $domainId,
            );
            $additionalServiceData->useProductVatRateByDomainId[$domainId] = $useProductVatRate;
            $additionalServiceData->vatsIndexedByDomainId[$domainId] = $useProductVatRate ? null : $vat;
            $additionalServiceData->showInFeedsByDomainId[$domainId] = $showInFeeds;
            $additionalServiceData->enabledByDomainId[$domainId] = $enabled;
        }

        $additionalService = $this->additionalServiceFacade->create($additionalServiceData);
        $this->addReference($referenceName, $additionalService);

        return $additionalService;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $additionalServices
     */
    private function assignAdditionalServicesToProduct(string $productReferenceSuffix, array $additionalServices): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceSuffix, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $productData->additionalServicesByDomainId[$domainId] = $additionalServices;
        }

        $this->productFacade->edit($product->getId(), $productData);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            VatDataFixture::class,
            CurrencyDataFixture::class,
            ProductDataFixture::class,
        ];
    }
}
