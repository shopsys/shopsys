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
     * @var string
     */
    private $mainCategoryPath;

    /**
     * @var bool
     */
    private bool $isAvailable;

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
     * @param string $mainCategoryPath
     * @param bool $isAvailable
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
        string $mainCategoryPath,
        bool $isAvailable
    ) {
        parent::__construct($id, $name, $shortDescription, $availability, $sellingPrice, $flagIds, $action, $image);

        $this->namePrefix = $namePrefix;
        $this->nameSufix = $nameSufix;
        $this->nonSellingPrice = $nonSellingPrice;
        $this->productAvailableStocksCountInformation = $productAvailableStocksCountInformation;
        $this->productCountExposedInStores = $productCountExposedInStores;
        $this->mainCategoryPath = $mainCategoryPath;
        $this->isAvailable = $isAvailable;
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
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }
}
