<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class MergadoFeedItem implements FeedItemInterface
{
    protected const string CATEGORY_PATH_SEPARATOR = ' > ';
    protected const string SHORT_DESCRIPTION_SEPARATOR = '. ';

    /**
     * @param string[] $galleryImageUrls
     * @param string[] $flags
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $productNo,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly array $categoryPath,
        protected readonly array $shortDescriptionUsp,
        protected readonly int|string $deliveryDays,
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

    #[Override]
    public function getSeekId(): int
    {
        return $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCategoryPath(): string
    {
        return implode(static::CATEGORY_PATH_SEPARATOR, $this->categoryPath);
    }

    public function getShortDescription(): string
    {
        return implode(static::SHORT_DESCRIPTION_SEPARATOR, $this->shortDescriptionUsp);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDeliveryDays(): int|string
    {
        return $this->deliveryDays;
    }

    public function getPrice(): PriceInterface
    {
        return $this->price;
    }

    public function getParameters(): iterable
    {
        return $this->parameters;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

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

    public function getProductNo(): string
    {
        return $this->productNo;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getMainVariantId(): ?int
    {
        return $this->mainVariantId;
    }

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

    public function getAvailability(): string
    {
        return $this->availability;
    }
}
