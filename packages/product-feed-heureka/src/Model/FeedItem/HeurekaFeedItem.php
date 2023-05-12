<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class HeurekaFeedItem implements FeedItemInterface
{
    /**
     * @param int $id
     * @param int|null $mainVariantId
     * @param string $name
     * @param string|null $description
     * @param string $url
     * @param string|null $imgUrl
     * @param string|null $brandName
     * @param string|null $ean
     * @param int|null $availabilityDispatchTime
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string|null $heurekaCategoryFullName
     * @param array $parametersByName
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $cpc
     */
    public function __construct(
        protected readonly int $id,
        protected readonly ?int $mainVariantId = null,
        protected readonly string $name,
        protected readonly ?string $description = null,
        protected readonly string $url,
        protected readonly ?string $imgUrl = null,
        protected readonly ?string $brandName = null,
        protected readonly ?string $ean = null,
        protected readonly ?int $availabilityDispatchTime = null,
        protected readonly Price $price,
        protected readonly ?string $heurekaCategoryFullName = null,
        protected readonly array $parametersByName,
        protected readonly ?Money $cpc = null
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
        return $this->heurekaCategoryFullName;
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
    public function getCpc(): ?Money
    {
        return $this->cpc;
    }
}
