<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;

class ProductDetailView
{
    /**
     * @param int $id
     * @param string|null $name
     * @param string|null $description
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null $sellingPrice
     * @param string|null $catnum
     * @param string|null $partno
     * @param string|null $ean
     * @param int|null $mainCategoryId
     * @param bool $isSellingDenied
     * @param bool $isInStock
     * @param bool $isMainVariant
     * @param int|null $mainVariantId
     * @param int[] $flagIds
     * @param string|null $seoPageTitle
     * @param string|null $seoMetaDescription
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $actionView
     * @param \Shopsys\ReadModelBundle\Brand\BrandView|null $brandView
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $mainImageView
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $galleryImageViews
     * @param \Shopsys\ReadModelBundle\Parameter\ParameterView[] $parameterViews
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[] $accessories
     * @param \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[] $variants
     */
    public function __construct(
        protected readonly int $id,
        protected readonly ?string $name,
        protected readonly ?string $description,
        protected readonly string $availability,
        protected readonly ?ProductPrice $sellingPrice = null,
        protected readonly ?string $catnum,
        protected readonly ?string $partno,
        protected readonly ?string $ean,
        protected readonly ?int $mainCategoryId = null,
        protected readonly bool $isSellingDenied,
        protected readonly bool $isInStock,
        protected readonly bool $isMainVariant,
        protected readonly ?int $mainVariantId = null,
        protected readonly array $flagIds,
        protected readonly ?string $seoPageTitle,
        protected readonly ?string $seoMetaDescription,
        protected readonly ProductActionView $actionView,
        protected readonly ?BrandView $brandView = null,
        protected readonly ?ImageView $mainImageView = null,
        protected readonly array $galleryImageViews,
        protected readonly array $parameterViews,
        protected readonly array $accessories,
        protected readonly array $variants
    ) {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getSellingPrice(): ?ProductPrice
    {
        return $this->sellingPrice;
    }

    /**
     * @return string|null
     */
    public function getCatnum(): ?string
    {
        return $this->catnum;
    }

    /**
     * @return string|null
     */
    public function getPartno(): ?string
    {
        return $this->partno;
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        return $this->ean;
    }

    /**
     * @return int|null
     */
    public function getMainCategoryId(): ?int
    {
        return $this->mainCategoryId;
    }

    /**
     * @return bool
     */
    public function isIsSellingDenied(): bool
    {
        return $this->isSellingDenied;
    }

    /**
     * @return bool
     */
    public function isInStock(): bool
    {
        return $this->isInStock;
    }

    /**
     * @return bool
     */
    public function isMainVariant(): bool
    {
        return $this->isMainVariant;
    }

    /**
     * @return bool
     */
    public function isVariant(): bool
    {
        return $this->mainVariantId !== null;
    }

    /**
     * @return int|null
     */
    public function getMainVariantId(): ?int
    {
        return $this->mainVariantId;
    }

    /**
     * @return int[]
     */
    public function getFlagIds(): array
    {
        return $this->flagIds;
    }

    /**
     * @return string|null
     */
    public function getSeoPageTitle(): ?string
    {
        return $this->seoPageTitle;
    }

    /**
     * @return string|null
     */
    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    public function getActionView(): ProductActionView
    {
        return $this->actionView;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Image\ImageView|null
     */
    public function getMainImageView(): ?ImageView
    {
        return $this->mainImageView;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Brand\BrandView|null
     */
    public function getBrandView(): ?BrandView
    {
        return $this->brandView;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Image\ImageView[]
     */
    public function getGalleryImageViews(): array
    {
        return $this->galleryImageViews;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView[]
     */
    public function getParameterViews(): array
    {
        return $this->parameterViews;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getAccessories(): array
    {
        return $this->accessories;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }
}
