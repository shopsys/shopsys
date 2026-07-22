<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class ZboziFeedItem implements FeedItemInterface
{
    /**
     * @param array<int, array{extraMessage: string, customText: string|null}> $additionalServices
     */
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
        protected readonly int|string|null $deliveryDate = null,
        protected readonly ?Money $cpc = null,
        protected readonly ?Money $cpcSearch = null,
        protected readonly array $additionalServices = [],
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

    /**
     * Returns the number of days until dispatch, or the expected restocking date in the "Y-m-d" format
     * when the awaited restocking is too far away for a number of days to be informative
     */
    public function getDeliveryDate(): int|string|null
    {
        return $this->deliveryDate;
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

    /**
     * @return array<int, array{extraMessage: string, customText: string|null}>
     */
    public function getAdditionalServices(): array
    {
        return $this->additionalServices;
    }
}
