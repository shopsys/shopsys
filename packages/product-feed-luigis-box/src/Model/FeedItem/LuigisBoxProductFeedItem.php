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
     * @param int $id
     * @param string $name
     * @param string $catalogNumber
     * @param string $availabilityText
     * @param int $availabilityRank
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basicPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $mainCategoryId
     * @param string $url
     * @param array<int, string> $categoryHierarchyNamesByCategoryId
     * @param bool $isMainVariant
     * @param string[] $flagNames
     * @param array<string, string> $productParameterValuesIndexedByName
     * @param string|null $mainCategoryName
     * @param string|null $ean
     * @param string|null $catnum
     * @param string|null $brandName
     * @param string|null $description
     * @param int|null $mainVariantId
     * @param string|null $imageUrlS
     * @param string|null $imageUrlM
     * @param string|null $imageUrlL
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $catalogNumber,
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
        protected readonly ?string $mainCategoryName,
        protected readonly ?string $ean,
        protected readonly ?string $catnum,
        protected readonly ?string $brandName,
        protected readonly ?string $description,
        protected readonly ?int $mainVariantId = null,
        protected readonly ?string $imageUrlS = null,
        protected readonly ?string $imageUrlM = null,
        protected readonly ?string $imageUrlL = null,
    ) {
    }

    /**
     * @return int
     */
    #[Override]
    public function getSeekId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return static::UNIQUE_IDENTIFIER_PREFIX . '-' . $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brandName;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getImageLinkS(): ?string
    {
        return $this->imageUrlS;
    }

    /**
     * @return string|null
     */
    public function getImageLinkM(): ?string
    {
        return $this->imageUrlM;
    }

    /**
     * @return string|null
     */
    public function getImageLinkL(): ?string
    {
        return $this->imageUrlL;
    }

    /**
     * @return string
     */
    public function getAvailabilityRankText(): string
    {
        return $this->availabilityText;
    }

    /**
     * @return int
     */
    public function getAvailabilityRank(): int
    {
        return $this->availabilityRank;
    }

    /**
     * @return int
     */
    public function getAvailability(): int
    {
        return self::SELLABLE_PRODUCT_AVAILABILITY;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getOldPrice(): ?Money
    {
        if ($this->price->equals($this->basicPrice)) {
            return null;
        }

        return $this->basicPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return string|null
     */
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

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        return $this->ean;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->catalogNumber;
    }

    /**
     * @return string|null
     */
    public function getProductCode(): ?string
    {
        return $this->catnum;
    }

    /**
     * @return int
     */
    public function getMainCategoryId(): int
    {
        return $this->mainCategoryId;
    }
}
