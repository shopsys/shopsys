<?php

namespace Shopsys\ShopBundle\DataFixtures\Loader;

use Faker\Generator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;

class ProductDataFixtureLoader
{
    const FAKER_SEED_NUMBER = 1;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureReferenceLoader
     */
    private $productDataFixtureReferenceLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\ShopBundle\Model\Category\Category[]|null
     */
    protected $referenceCategories;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]|null
     */
    protected $referenceAvailabilities;

    /**
     * @var \Shopsys\ShopBundle\Model\Category\Category[]|null
     */
    protected $referenceVats;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]|null
     */
    protected $referenceParameters;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]|null
     */
    protected $referenceFlags;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Brand\Brand[]
     */
    private $referenceBrands;

    /**
     * @var string[]
     */
    private $referenceUnits;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\ShopBundle\DataFixtures\Loader\ProductDataFixtureReferenceLoader $productDataFixtureReferenceLoader
     */
    public function __construct(
        Domain $domain,
        Generator $faker,
        ProductDataFactory $productDataFactory,
        ProductDataFixtureReferenceLoader $productDataFixtureReferenceLoader
    ) {
        $this->domain = $domain;
        $this->faker = $faker;
        $this->productDataFactory = $productDataFactory;
        $this->productDataFixtureReferenceLoader = $productDataFixtureReferenceLoader;
    }

    public function loadReferences()
    {
        $this->referenceCategories = $this->productDataFixtureReferenceLoader->getCategoryReferences();
        $this->referenceVats = $this->productDataFixtureReferenceLoader->getVatReferences();
        $this->referenceAvailabilities = $this->productDataFixtureReferenceLoader->getAvailabilityReferences();
        $this->referenceFlags = $this->productDataFixtureReferenceLoader->getFlagReferences();
        $this->referenceBrands = $this->productDataFixtureReferenceLoader->getBrandReferences();
        $this->referenceUnits = $this->productDataFixtureReferenceLoader->getUnitReferences();
    }

    /**
     * @param int $fakerSeedNumber
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData
     */
    public function getProductsDataForFakerSeed(int $fakerSeedNumber)
    {
        $this->faker->seed($fakerSeedNumber);
        $productData = $this->productDataFactory->create();
        $locales = $this->domain->getAllLocales();
        $domainConfigs = $this->domain->getAll();

        $productData->brand = $this->faker->randomElement($this->referenceBrands);
        $productData->vat = $this->faker->randomElement($this->referenceVats);
        $productData->availability = $this->faker->randomElement($this->referenceAvailabilities);
        $productData->flags = $this->faker->randomElements($this->referenceFlags);
        $productData->unit = $this->faker->randomElement($this->referenceUnits);

        foreach ($locales as $locale) {
            $productData->name[$locale] = $this->faker->bothify($productData->brand->getName() . ' ###???');
        }

        $productData->partno = $this->faker->lexify($this->faker->randomNumber(5) . '??');
        $productData->catnum = $this->faker->lexify('??' . $this->faker->randomNumber(5) . '??');
        $productData->ean = $this->faker->randomNumber(8);

        $description = $this->faker->sentence(200);
        foreach ($domainConfigs as $domainConfig) {
            $domainId = $domainConfig->getId();
            $productData->descriptions[$domainId] = $description;
            $productData->shortDescriptions[$domainId] = substr($description, 0, 100);

            $pricingGroups = $this->productDataFixtureReferenceLoader->getPricingGroupReferencesByDomainId($domainId);
            foreach ($pricingGroups as $pricingGroup) {
                $maxDigit = $domainId == 1 ? 3 : 4;
                $productData->manualInputPricesByPricingGroupId[$pricingGroup->getId()] = Money::create((string)$this->faker->randomFloat($maxDigit));
            }
        }

        $productQuantity = 0;
        $productData->sellingDenied = $this->faker->boolean(20);
        $isProductInStock = $this->faker->boolean(80);

        if ($isProductInStock) {
            $productQuantity = $this->faker->randomNumber(4);
        }

        $productData->usingStock = $isProductInStock;
        $productData->stockQuantity = $productQuantity;
        $productData->outOfStockAction = Product::OUT_OF_STOCK_ACTION_HIDE;

        $isSellable = $this->faker->boolean(85);
        $sellingFrom = null;

        if ($isSellable) {
            $sellingFrom = $this->faker->dateTimeThisCentury('now');
        }

        $productData->sellingFrom = $sellingFrom;

        foreach ($domainConfigs as $domainConfig) {
            $domainId = $domainConfig->getId();
            $productData->categoriesByDomainId[$domainId] = $this->faker->randomElements($this->referenceCategories);
        }

        return $productData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $numberOfVariants
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductData[]
     */
    public function createVariantsProductDataForProduct(Product $product, int $numberOfVariants)
    {
        $variants = [];
        $count = 0;

        while (++$count < $numberOfVariants) {
            $productData = $this->productDataFactory->createFromProduct($product);
            $productData->catnum .= $this->faker->lexify('??');

            $locales = $this->domain->getAllLocales();
            foreach ($locales as $locale) {
                $productData->name[$locale] .= $this->faker->lexify(' ??');
            }
            $variants[] = $productData;
        }

        return $variants;
    }
}
