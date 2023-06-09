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

    protected int $id;

    protected string $name;

    protected string $brandName;

    protected string $description;

    protected ?string $ean = null;

    protected ?string $partno = null;

    protected string $url;

    protected ?string $imgUrl = null;

    protected bool $sellingDenied;

    protected Price $price;

    protected Currency $currency;

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
        int $id,
        string $name,
        ?string $brandName,
        ?string $description,
        ?string $ean,
        ?string $partno,
        string $url,
        ?string $imgUrl,
        bool $sellingDenied,
        Price $price,
        Currency $currency
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->brandName = $brandName;
        $this->description = $description;
        $this->ean = $ean;
        $this->partno = $partno;
        $this->url = $url;
        $this->imgUrl = $imgUrl;
        $this->sellingDenied = $sellingDenied;
        $this->price = $price;
        $this->currency = $currency;
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
