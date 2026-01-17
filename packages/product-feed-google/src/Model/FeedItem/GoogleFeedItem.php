<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;

class GoogleFeedItem implements FeedItemInterface
{
    protected const IDENTIFIER_TYPE_EAN = 'gtin';
    protected const IDENTIFIER_TYPE_PARTNO = 'mpn';
    protected const AVAILABILITY_OUT_OF_STOCK = 'out_of_stock';
    protected const AVAILABILITY_IN_STOCK = 'in_stock';

    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly bool $sellingDenied,
        protected readonly PriceInterface $price,
        protected readonly ?SpecialPrice $specialPrice,
        protected readonly Currency $currency,
        protected readonly string $url,
        protected readonly ?string $brandName,
        protected readonly ?string $description,
        protected readonly ?string $ean = null,
        protected readonly ?string $partno = null,
        protected readonly ?string $imgUrl = null,
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

    public function getLink(): string
    {
        return $this->url;
    }

    public function getImageLink(): ?string
    {
        return $this->imgUrl;
    }

    public function getAvailability(): string
    {
        return $this->sellingDenied ? static::AVAILABILITY_OUT_OF_STOCK : static::AVAILABILITY_IN_STOCK;
    }

    public function getPrice(): PriceInterface
    {
        return $this->price;
    }

    public function getSpecialPrice(): ?SpecialPrice
    {
        return $this->specialPrice;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return string[]
     */
    public function getIdentifiers(): array
    {
        return array_filter([
            static::IDENTIFIER_TYPE_EAN => $this->ean,
            static::IDENTIFIER_TYPE_PARTNO => $this->partno,
        ]);
    }
}
