<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductView as BaseListedProductView;

class ListedProductView extends BaseListedProductView
{
    /**
     * @var string|null
     */
    private $namePrefix;

    /**
     * @var string|null
     */
    private $nameSufix;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    private $nonSellingPrice;

    /**
     * @var string|null
     */
    private $productAvailableStocksCountInformation;

    /**
     * @var string|null
     */
    private $productCountExposedInStores;

    /**
     * @var array|null
     */
    private $variantsParametersSetup;

    /**
     * @var string|null
     */
    private $variantUrl;

    /**
     * @var string|null
     */
    private $variantImageUrl;

    /**
     * @var string
     */
    private $mainCategoryPath;

    /**
     * @var int
     */
    private $countColorsInVariants;

    /**
     * @var int
     */
    private $countDifferentVariants;

    /**
     * @var bool
     */
    private $hasScontoFlag;

    /**
     * @param int $id
     * @param string $name
     * @param string|null $shortDescription
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param array $flagIds
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $action
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $image
     * @param string|null $namePrefix
     * @param string|null $nameSufix
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null $nonSellingPrice
     * @param string $productAvailableStocksCountInformation
     * @param string $productCountExposedInStores
     * @param array|null $variantsParametersSetup
     * @param string $mainCategoryPath
     * @param int $countColorsInVariants
     * @param int $countDifferentVariants
     * @param bool $hasScontoFlag
     */
    public function __construct(
        int $id,
        string $name,
        ?string $shortDescription,
        string $availability,
        ProductPrice $sellingPrice,
        array $flagIds,
        ProductActionView $action,
        ?ImageView $image,
        ?string $namePrefix,
        ?string $nameSufix,
        ?ProductPrice $nonSellingPrice,
        string $productAvailableStocksCountInformation,
        string $productCountExposedInStores,
        ?array $variantsParametersSetup,
        string $mainCategoryPath,
        int $countColorsInVariants,
        int $countDifferentVariants,
        bool $hasScontoFlag
    ) {
        parent::__construct($id, $name, $shortDescription, $availability, $sellingPrice, $flagIds, $action, $image);

        $this->namePrefix = $namePrefix;
        $this->nameSufix = $nameSufix;
        $this->nonSellingPrice = $nonSellingPrice;
        $this->productAvailableStocksCountInformation = $productAvailableStocksCountInformation;
        $this->productCountExposedInStores = $productCountExposedInStores;
        $this->variantsParametersSetup = $variantsParametersSetup;
        $this->mainCategoryPath = $mainCategoryPath;
        $this->countColorsInVariants = $countColorsInVariants;
        $this->countDifferentVariants = $countDifferentVariants;
        $this->hasScontoFlag = $hasScontoFlag;
    }

    /**
     * @return string|null
     */
    public function getNamePrefix(): ?string
    {
        return $this->namePrefix;
    }

    /**
     * @return string|null
     */
    public function getNameSufix(): ?string
    {
        return $this->nameSufix;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getNonSellingPrice(): ?ProductPrice
    {
        return $this->nonSellingPrice;
    }

    /**
     * @return string|null
     */
    public function getProductAvailableStocksCountInformation(): ?string
    {
        return $this->productAvailableStocksCountInformation;
    }

    /**
     * @return string|null
     */
    public function getProductCountExposedInStores(): ?string
    {
        return $this->productCountExposedInStores;
    }

    /**
     * @return array|null
     */
    public function getVariantsParametersSetup(): ?array
    {
        return $this->variantsParametersSetup;
    }

    /**
     * @param int $variantId
     */
    public function deleteVariantParametersSetupByVariantId(int $variantId): void
    {
        unset($this->variantsParametersSetup[$variantId]);
    }

    /**
     * @return string|null
     */
    public function getVariantUrl(): ?string
    {
        return $this->variantUrl;
    }

    /**
     * @param string|null $variantUrl
     */
    public function setVariantUrl(?string $variantUrl): void
    {
        $this->variantUrl = $variantUrl;
    }

    /**
     * @return string|null
     */
    public function getVariantImageUrl(): ?string
    {
        return $this->variantImageUrl;
    }

    /**
     * @param string|null $variantImageUrl
     */
    public function setVariantImageUrl(?string $variantImageUrl): void
    {
        $this->variantImageUrl = $variantImageUrl;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return trim(sprintf('%s %s %s', $this->namePrefix, $this->name, $this->nameSufix));
    }

    /**
     * @return string
     */
    public function getMainCategoryPath(): string
    {
        return $this->mainCategoryPath;
    }

    /**
     * @return int
     */
    public function getCountColorsInVariants(): int
    {
        return $this->countColorsInVariants;
    }

    /**
     * @return int
     */
    public function getCountDifferentVariants(): int
    {
        return $this->countDifferentVariants;
    }

    /**
     * @return bool
     */
    public function hasScontoFlag(): bool
    {
        return $this->hasScontoFlag;
    }
}
