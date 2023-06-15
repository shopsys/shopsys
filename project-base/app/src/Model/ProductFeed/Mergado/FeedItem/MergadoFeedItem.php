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
    public const FLAGS_MAP = [
        1 => 'Akce',
        2 => 'Cenový HIT',
        3 => 'Novinka',
        4 => 'Výprodej',
        5 => 'Vyrobeno v CZ',
        6 => 'Vyrobeno v DE',
        7 => 'Vyrobeno v SK',
    ];

    private string $description;

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
     * @param string $currencyCode
     * @param string|null $description
     * @param \App\Model\Product\Brand\Brand|null $brand
     * @param string|null $imageUrl
     * @param int|null $mainVariantId
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $highProductPrice
     * @param string[] $flags
     * @param int|null $availability
     */
    public function __construct(
        private int $id,
        private string $productNo,
        private string $name,
        private string $url,
        private array $categoryPath,
        private array $shortDescriptionUsp,
        private int $deliveryDays,
        private ProductPrice $price,
        private array $galleryImageUrls,
        private array $parameters,
        private string $currencyCode,
        ?string $description,
        private ?Brand $brand = null,
        private ?string $imageUrl = null,
        private ?int $mainVariantId = null,
        private ProductPrice $highProductPrice,
        private array $flags,
        private ?int $availability,
    ) {
        $this->description = $description;
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
        return implode(self::CATEGORY_PATH_SEPARATOR, $this->categoryPath);
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return implode(self::SHORT_DESCRIPTION_SEPARATOR, $this->shortDescriptionUsp);
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getHighProductPrice(): ProductPrice
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
     * @return int|null
     */
    public function getAvailability(): ?int
    {
        return $this->availability;
    }
}
