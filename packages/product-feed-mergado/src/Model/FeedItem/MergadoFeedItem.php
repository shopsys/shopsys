<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class MergadoFeedItem implements FeedItemInterface
{
    protected const string CATEGORY_PATH_SEPARATOR = ' > ';
    protected const string SHORT_DESCRIPTION_SEPARATOR = '. ';

    /**
     * @param int $id
     * @param string $productNo
     * @param string $name
     * @param string $url
     * @param array $categoryPath
     * @param array $shortDescriptionUsp
     * @param int $deliveryDays
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $price
     * @param string[] $galleryImageUrls
     * @param array $parameters
     * @param string $currencyCode
     * @param string|null $description
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $highProductPrice
     * @param string[] $flags
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null $brand
     * @param string|null $imageUrl
     * @param int|null $mainVariantId
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $productNo,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly array $categoryPath,
        protected readonly array $shortDescriptionUsp,
        protected readonly int $deliveryDays,
        protected readonly PriceInterface $price,
        protected readonly array $galleryImageUrls,
        protected readonly array $parameters,
        protected readonly string $currencyCode,
        protected readonly ?string $description,
        protected readonly PriceInterface $highProductPrice,
        protected readonly array $flags,
        protected readonly string $availability,
        protected readonly ?Brand $brand = null,
        protected readonly ?string $imageUrl = null,
        protected readonly ?int $mainVariantId = null,
    ) {
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getPrice(): PriceInterface
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null
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
     * @return string[]
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
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @return int|null
     */
    public function getMainVariantId(): ?int
    {
        return $this->mainVariantId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getHighProductPrice(): PriceInterface
    {
        return $this->highProductPrice;
    }

    /**
     * @return string[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }
}
