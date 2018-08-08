<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class HeurekaFeedItem implements FeedItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int|null
     */
    protected $mainVariantId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $imgUrl;

    /**
     * @var string|null
     */
    protected $brandName;

    /**
     * @var string|null
     */
    protected $ean;

    /**
     * @var int|null
     */
    protected $availabilityDispatchTime;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected $price;

    /**
     * @var string|null
     */
    protected $heurekaCategoryFullName;

    /**
     * @var string[]
     */
    protected $parametersByName;

    /**
     * @var float|null
     */
    protected $cpc;

    public function __construct(
        int $id,
        ?int $mainVariantId,
        string $name,
        ?string $description,
        string $url,
        ?string $imgUrl,
        ?string $brandName,
        ?string $ean,
        ?int $availabilityDispatchTime,
        Price $price,
        ?string $heurekaCategoryFullName,
        array $parametersByName,
        ?float $cpc
    ) {
        $this->id = $id;
        $this->mainVariantId = $mainVariantId;
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->imgUrl = $imgUrl;
        $this->brandName = $brandName;
        $this->ean = $ean;
        $this->availabilityDispatchTime = $availabilityDispatchTime;
        $this->price = $price;
        $this->heurekaCategoryFullName = $heurekaCategoryFullName;
        $this->parametersByName = $parametersByName;
        $this->cpc = $cpc;
    }

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

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getEan(): ?string
    {
        return $this->ean;
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
        return $this->heurekaCategoryFullName;
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->parametersByName;
    }

    public function getCpc(): ?float
    {
        return $this->cpc;
    }
}
