<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ZboziFeedItem implements FeedItemInterface
{
    const CATEGORY_PATH_SEPARATOR = ' | ';

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
     * @var string|null
     */
    protected $partno;

    /**
     * @var int|null
     */
    protected $availabilityDispatchTime;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected $price;

    /**
     * @var string[]
     */
    protected $pathToMainCategory;

    /**
     * @var string[]
     */
    protected $parametersByName;

    /**
     * @var float|null
     */
    protected $cpc;

    /**
     * @var float|null
     */
    protected $cpcSearch;

    public function __construct(
        int $id,
        ?int $mainVariantId,
        string $name,
        ?string $description,
        string $url,
        ?string $imgUrl,
        ?string $brandName,
        ?string $ean,
        ?string $partno,
        ?int $availabilityDispatchTime,
        Price $price,
        array $pathToMainCategory,
        array $parametersByName,
        ?float $cpc,
        ?float $cpcSearch
    ) {
        $this->id = $id;
        $this->mainVariantId = $mainVariantId;
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->imgUrl = $imgUrl;
        $this->brandName = $brandName;
        $this->ean = $ean;
        $this->partno = $partno;
        $this->availabilityDispatchTime = $availabilityDispatchTime;
        $this->price = $price;
        $this->pathToMainCategory = $pathToMainCategory;
        $this->parametersByName = $parametersByName;
        $this->cpc = $cpc;
        $this->cpcSearch = $cpcSearch;
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
        return implode(self::CATEGORY_PATH_SEPARATOR, $this->pathToMainCategory);
    }

    /**
     * @return string[]
     */
    public function getParams(): array
    {
        return $this->parametersByName;
    }

    public function getMaxCpc(): ?float
    {
        return $this->cpc;
    }

    public function getMaxCpcSearch(): ?float
    {
        return $this->cpcSearch;
    }
}
