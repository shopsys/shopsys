<?php

namespace Shopsys\ProductFeed\ZboziBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ZboziFeedItem implements FeedItemInterface
{
    protected const CATEGORY_PATH_SEPARATOR = ' | ';

    protected int $id;

    protected ?int $mainVariantId = null;

    protected string $name;

    protected ?string $description = null;

    protected string $url;

    protected ?string $imgUrl = null;

    protected ?string $brandName = null;

    protected ?string $ean = null;

    protected ?string $partno = null;

    protected ?int $availabilityDispatchTime = null;

    protected Price $price;

    /**
     * @var string[]
     */
    protected array $pathToMainCategory;

    /**
     * @var string[]
     */
    protected array $parametersByName;

    protected ?Money $cpc = null;

    protected ?Money $cpcSearch = null;

    /**
     * @param int $id
     * @param int|null $mainVariantId
     * @param string $name
     * @param string|null $description
     * @param string $url
     * @param string|null $imgUrl
     * @param string|null $brandName
     * @param string|null $ean
     * @param string|null $partno
     * @param int|null $availabilityDispatchTime
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param array $pathToMainCategory
     * @param array $parametersByName
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $cpc
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $cpcSearch
     */
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
        ?Money $cpc,
        ?Money $cpcSearch
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
