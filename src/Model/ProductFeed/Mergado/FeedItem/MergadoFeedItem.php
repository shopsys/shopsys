<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado\FeedItem;

use App\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;

class MergadoFeedItem implements FeedItemInterface
{
    private const CATEGORY_PATH_SEPARATOR = ' > ';
    private const SHORT_DESCRIPTION_SEPARATOR = '. ';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $categoryPath;

    /**
     * @var array
     */
    private $shortDescriptionUsp;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $deliveryDays;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    private $price;

    /**
     * @var null|\App\Model\Product\Brand\Brand
     */
    private $brand;

    /**
     * @var null|string
     */
    private $imageUrl;

    /**
     * @var array
     */
    private $galleryImageUrls;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $productNo;

    /**
     * @var int|null
     */
    private $mainVariantId;

    /**
     * @param int $id
     * @param string $productNo
     * @param string $name
     * @param string $url
     * @param array $categoryPath
     * @param array $shortDescriptionUsp
     * @param int $deliveryDays
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $price
     * @param array $galleryImageUrls
     * @param array $parameters
     * @param string|null $description
     * @param \App\Model\Product\Brand\Brand|null $brand
     * @param string|null $imageUrl
     * @param int|null $mainVariantId
     */
    public function __construct(
        int $id,
        string $productNo,
        string $name,
        string $url,
        array $categoryPath,
        array $shortDescriptionUsp,
        int $deliveryDays,
        ProductPrice $price,
        array $galleryImageUrls,
        array $parameters,
        ?string $description,
        ?Brand $brand,
        ?string $imageUrl,
        ?int $mainVariantId = null
    ) {
        $this->id = $id;
        $this->productNo = $productNo;
        $this->name = $name;
        $this->url = $url;
        $this->categoryPath = $categoryPath;
        $this->shortDescriptionUsp = $shortDescriptionUsp;
        $this->description = $description;
        $this->deliveryDays = $deliveryDays;
        $this->price = $price;
        $this->brand = $brand;
        $this->imageUrl = $imageUrl;
        $this->galleryImageUrls = $galleryImageUrls;
        $this->parameters = $parameters;
        $this->mainVariantId = $mainVariantId;
    }

    /**
     * @return int
     */
    public function getSeekId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getCategoryPath(): string
    {
        return implode(static::CATEGORY_PATH_SEPARATOR, $this->categoryPath);
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return implode(static::SHORT_DESCRIPTION_SEPARATOR, $this->shortDescriptionUsp);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getDeliveryDays(): int
    {
        return $this->deliveryDays;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getPrice(): ProductPrice
    {
        return $this->price;
    }

    /**
     * @return iterable
     */
    public function getParameters(): iterable
    {
        return $this->parameters;
    }

    /**
     * @return \App\Model\Product\Brand\Brand|null
     */
    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return array
     */
    public function getGalleryImageUrls(): array
    {
        return $this->galleryImageUrls;
    }

    /**
     * @return string
     */
    public function getProductNo(): string
    {
        return $this->productNo;
    }

    /**
     * @return int|null
     */
    public function getMainVariantId(): ?int
    {
        return $this->mainVariantId;
    }
}
