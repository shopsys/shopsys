<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ZboziFeedItem implements FeedItemInterface
{
    protected const CATEGORY_PATH_SEPARATOR = ' | ';

    /**
     * @param int $id
     * @param string $name
     * @param string $url
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param array $pathToMainCategory
     * @param array $parametersByName
     * @param int|null $mainVariantId
     * @param string|null $description
     * @param string|null $imgUrl
     * @param string|null $brandName
     * @param string|null $ean
     * @param string|null $partno
     * @param int|null $availabilityDispatchTime
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $cpc
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $cpcSearch
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly Price $price,
        protected readonly array $pathToMainCategory,
        protected readonly array $parametersByName,
        protected readonly ?int $mainVariantId = null,
        protected readonly ?string $description = null,
        protected readonly ?string $imgUrl = null,
        protected readonly ?string $brandName = null,
        protected readonly ?string $ean = null,
        protected readonly ?string $partno = null,
        protected readonly ?int $availabilityDispatchTime = null,
        protected readonly ?Money $cpc = null,
        protected readonly ?Money $cpcSearch = null
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
     * @return int|null
     */
    public function getGroupId(): ?int
    {
        return $this->mainVariantId;
    }

    /**
     * @return string
     */
    public function getName(): string
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        return $this->ean;
    }

    /**
     * @return string|null
     */
    public function getProductno(): ?string
    {
        return $this->partno;
    }

    /**
     * @return int|null
     */
    public function getDeliveryDate(): ?int
    {
        return $this->availabilityDispatchTime;
    }

    /**
     * @return string|null
     */
    public function getManufacturer(): ?string
    {
        return $this->brandName;
    }

    /**
     * @return string|null
     */
    public function getCategoryText(): ?string
    {
        return implode(static::CATEGORY_PATH_SEPARATOR, $this->pathToMainCategory);
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->parametersByName;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getMaxCpc(): ?Money
    {
        return $this->cpc;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getMaxCpcSearch(): ?Money
    {
        return $this->cpcSearch;
    }
}
