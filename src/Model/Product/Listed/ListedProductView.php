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
        string $productCountExposedInStores
    ) {
        parent::__construct($id, $name, $shortDescription, $availability, $sellingPrice, $flagIds, $action, $image);

        $this->namePrefix = $namePrefix;
        $this->nameSufix = $nameSufix;
        $this->nonSellingPrice = $nonSellingPrice;
        $this->productAvailableStocksCountInformation = $productAvailableStocksCountInformation;
        $this->productCountExposedInStores = $productCountExposedInStores;
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
}
