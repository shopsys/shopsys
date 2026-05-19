<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class ZboziFeedItem implements FeedItemInterface
{
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly PriceInterface $price,
        protected readonly ?string $categoryText,
        protected readonly array $parametersByName,
        protected readonly ?int $mainVariantId = null,
        protected readonly ?string $description = null,
        protected readonly ?string $imgUrl = null,
        protected readonly ?string $brandName = null,
        protected readonly ?string $ean = null,
        protected readonly ?string $partno = null,
        protected readonly ?int $availabilityDispatchTime = null,
        protected readonly ?Money $cpc = null,
        protected readonly ?Money $cpcSearch = null,
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

    public function getGroupId(): ?int
    {
        return $this->mainVariantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImgUrl(): ?string
    {
        return $this->imgUrl;
    }

    public function getPrice(): PriceInterface
    {
        return $this->price;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function getProductno(): ?string
    {
        return $this->partno;
    }

    public function getDeliveryDate(): ?int
    {
        return $this->availabilityDispatchTime;
    }

    public function getManufacturer(): ?string
    {
        return $this->brandName;
    }

    public function getCategoryText(): ?string
    {
        return $this->categoryText;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->parametersByName;
    }

    public function getMaxCpc(): ?Money
    {
        return $this->cpc;
    }

    public function getMaxCpcSearch(): ?Money
    {
        return $this->cpcSearch;
    }
}
