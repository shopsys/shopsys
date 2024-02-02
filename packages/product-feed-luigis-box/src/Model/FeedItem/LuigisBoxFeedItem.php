<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class LuigisBoxFeedItem implements FeedItemInterface
{
    /**
     * @param int $id
     * @param string $name
     * @param string $catalogNumber
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $mainCategoryId
     * @param string $url
     * @param array<int, string> $categoryHierarchyNamesByCategoryId
     * @param array<int, string> $categoryHierarchyIdsByCategoryId
     * @param bool $isMainVariant
     * @param string[] $flagNames
     * @param array<string, string> $productParameterValuesIndexedByName
     * @param string|null $mainCategoryName
     * @param string|null $ean
     * @param string|null $partNo
     * @param string|null $brandName
     * @param string|null $description
     * @param string|null $imgUrl
     * @param int|null $mainVariantId
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $catalogNumber,
        protected readonly string $availability,
        protected readonly Price $price,
        protected readonly Currency $currency,
        protected readonly int $mainCategoryId,
        protected readonly string $url,
        protected readonly array $categoryHierarchyNamesByCategoryId,
        protected readonly array $categoryHierarchyIdsByCategoryId,
        protected readonly bool $isMainVariant,
        protected readonly array $flagNames,
        protected readonly array $productParameterValuesIndexedByName,
        protected readonly ?string $mainCategoryName,
        protected readonly ?string $ean,
        protected readonly ?string $partNo,
        protected readonly ?string $brandName,
        protected readonly ?string $description,
        protected readonly ?string $imgUrl = null,
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
    public function getLink(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getImageLink(): ?string
    {
        return $this->imgUrl;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->mainCategoryId;
    }

    /**
     * @return string|null
     */
    public function getCategoryText(): ?string
    {
        return $this->mainCategoryName;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return int|null
     */
    public function getItemGroupId(): ?int
    {
        return $this->mainVariantId;
    }

    /**
     * @return array<int, string>
     */
    public function getHierarchyNamesIndexedByCategoryId(): array
    {
        return $this->categoryHierarchyNamesByCategoryId;
    }

    /**
     * @return array<int, string>
     */
    public function getHierarchyIdsIndexedByCategoryId(): array
    {
        return $this->categoryHierarchyIdsByCategoryId;
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
     * @return bool
     */
    public function isMaster(): bool
    {
        return $this->isMainVariant;
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
    public function getPartNo(): ?string
    {
        return $this->partNo;
    }
}
