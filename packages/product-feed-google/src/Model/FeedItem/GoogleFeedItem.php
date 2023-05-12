<?php

namespace Shopsys\ProductFeed\GoogleBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class GoogleFeedItem implements FeedItemInterface
{
    protected const IDENTIFIER_TYPE_EAN = 'gtin';
    protected const IDENTIFIER_TYPE_PARTNO = 'mpn';
    protected const AVAILABILITY_OUT_OF_STOCK = 'out of stock';
    protected const AVAILABILITY_IN_STOCK = 'in stock';

    /**
     * @param int $id
     * @param string $name
     * @param string|null $brandName
     * @param string|null $description
     * @param string|null $ean
     * @param string|null $partno
     * @param string $url
     * @param string|null $imgUrl
     * @param bool $sellingDenied
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly ?string $brandName,
        protected readonly ?string $description,
        protected readonly ?string $ean = null,
        protected readonly ?string $partno = null,
        protected readonly string $url,
        protected readonly ?string $imgUrl = null,
        protected readonly bool $sellingDenied,
        protected readonly Price $price,
        protected readonly Currency $currency
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
        return $this->sellingDenied ? static::AVAILABILITY_OUT_OF_STOCK : static::AVAILABILITY_IN_STOCK;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
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
