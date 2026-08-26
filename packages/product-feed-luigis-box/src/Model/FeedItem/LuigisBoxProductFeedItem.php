<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class LuigisBoxProductFeedItem implements FeedItemInterface
{
    public const string UNIQUE_IDENTIFIER_PREFIX = 'product';
    protected const int SELLABLE_PRODUCT_AVAILABILITY = 1;

    /**
     * @param array<int, string> $categoryHierarchyNamesByCategoryId
     * @param string[] $flagNames
     * @param array<string, string> $productParameterValuesIndexedByName
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $availabilityText,
        protected readonly int $availabilityRank,
        protected readonly Money $price,
        protected readonly Money $basicPrice,
        protected readonly Currency $currency,
        protected readonly int $mainCategoryId,
        protected readonly string $url,
        protected readonly array $categoryHierarchyNamesByCategoryId,
        protected readonly bool $isMainVariant,
        protected readonly array $flagNames,
        protected readonly array $productParameterValuesIndexedByName,
        protected readonly ?string $ean,
        protected readonly ?string $catnum,
        protected readonly ?string $brandName,
        protected readonly ?string $description,
        protected readonly ?int $mainVariantId = null,
        protected readonly ?string $imageUrlS = null,
        protected readonly ?string $imageUrlM = null,
        protected readonly ?string $imageUrlL = null,
        protected readonly ?string $seoTitle = null,
        protected readonly ?string $seoMetaDescription = null,
        protected readonly ?string $seoH1 = null,
    ) {
    }

    #[Override]
    public function getSeekId(): int
    {
        return $this->id;
    }

    public function getIdentity(): string
    {
        return static::UNIQUE_IDENTIFIER_PREFIX . '-' . $this->id;
    }

    public function getTitle(): string
    {
        return $this->name;
    }

    public function getBrand(): ?string
    {
        return $this->brandName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImageLinkS(): ?string
    {
        return $this->imageUrlS;
    }

    public function getImageLinkM(): ?string
    {
        return $this->imageUrlM;
    }

    public function getImageLinkL(): ?string
    {
        return $this->imageUrlL;
    }

    public function getAvailabilityRankText(): string
    {
        return $this->availabilityText;
    }

    public function getAvailabilityRank(): int
    {
        return $this->availabilityRank;
    }

    public function getAvailability(): int
    {
        return self::SELLABLE_PRODUCT_AVAILABILITY;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getOldPrice(): ?Money
    {
        if ($this->price->equals($this->basicPrice)) {
            return null;
        }

        return $this->basicPrice;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getItemGroupId(): ?string
    {
        if ($this->isMainVariant) {
            return $this->getIdentity();
        }

        if ($this->mainVariantId === null) {
            return null;
        }

        return static::UNIQUE_IDENTIFIER_PREFIX . '-' . $this->mainVariantId;
    }

    /**
     * @return array<int, string>
     */
    public function getCategoryNamesIndexedByCategoryId(): array
    {
        return $this->categoryHierarchyNamesByCategoryId;
    }

    /**
     * @return string[]
     */
    public function getFlagNames(): array
    {
        return $this->flagNames;
    }

    /**
     * @return array<string, string>
     */
    public function getProductParameterValuesIndexedByName(): array
    {
        return $this->productParameterValuesIndexedByName;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function getProductCode(): ?string
    {
        return $this->catnum;
    }

    public function getMainCategoryId(): int
    {
        return $this->mainCategoryId;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }
}
