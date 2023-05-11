<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;

class ProductDetailView
{
    protected int $id;

    protected string $name;

    /**
     * @var int[]
     */
    protected array $flagIds;

    protected ?ImageView $mainImageView = null;

    protected ProductActionView $actionView;

    protected string $seoPageTitle;

    protected string $availability;

    protected bool $isInStock;

    protected bool $isSellingDenied;

    protected ?ProductPrice $sellingPrice = null;

    protected ?int $mainCategoryId = null;

    protected ?BrandView $brandView = null;

    protected string $catnum;

    protected string $partno;

    protected string $ean;

    protected string $description;

    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageView[]
     */
    protected array $galleryImageViews;

    protected string $seoMetaDescription;

    protected bool $isMainVariant;

    protected ?int $mainVariantId = null;

    /**
     * @var array
     */
    protected array $parameterViews;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    protected array $accessories;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    protected array $variants;

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
        int $id,
        ?string $name,
        ?string $description,
        string $availability,
        ?ProductPrice $sellingPrice,
        ?string $catnum,
        ?string $partno,
        ?string $ean,
        ?int $mainCategoryId,
        bool $isSellingDenied,
        bool $isInStock,
        bool $isMainVariant,
        ?int $mainVariantId,
        array $flagIds,
        ?string $seoPageTitle,
        ?string $seoMetaDescription,
        ProductActionView $actionView,
        ?BrandView $brandView,
        ?ImageView $mainImageView,
        array $galleryImageViews,
        array $parameterViews,
        array $accessories,
        array $variants
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->availability = $availability;
        $this->sellingPrice = $sellingPrice;
        $this->catnum = $catnum;
        $this->partno = $partno;
        $this->ean = $ean;
        $this->mainCategoryId = $mainCategoryId;
        $this->isSellingDenied = $isSellingDenied;
        $this->isInStock = $isInStock;
        $this->isMainVariant = $isMainVariant;
        $this->mainVariantId = $mainVariantId;
        $this->flagIds = $flagIds;
        $this->seoPageTitle = $seoPageTitle;
        $this->seoMetaDescription = $seoMetaDescription;
        $this->actionView = $actionView;
        $this->mainImageView = $mainImageView;
        $this->brandView = $brandView;
        $this->galleryImageViews = $galleryImageViews;
        $this->parameterViews = $parameterViews;
        $this->accessories = $accessories;
        $this->variants = $variants;
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
