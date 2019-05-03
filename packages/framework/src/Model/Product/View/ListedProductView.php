<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\View;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Component\Image\View\ImageView;

/**
 * @experimental
 *
 * Class representing products in lists in FE templates (to avoid usage of Doctrine entities a hence achieve performance gain)
 */
class ListedProductView
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\View\ImageView|null
     */
    protected $image;

    /**
     * @var string
     */
    protected $availability;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    protected $sellingPrice;

    /**
     * @var ?string
     */
    protected $shortDescription;

    /**
     * @var int[]
     */
    protected $flagIds = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\View\ProductActionView
     */
    protected $action;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\View\ImageView|null
     */
    public function getImage(): ?ImageView
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getSellingPrice(): ProductPrice
    {
        return $this->sellingPrice;
    }

    /**
     * @return string!null|
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @return int[]
     */
    public function getFlagIds(): array
    {
        return $this->flagIds;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\View\ProductActionView
     */
    public function getAction(): ProductActionView
    {
        return $this->action;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\View\ImageView|null $image
     */
    public function setImage(?ImageView $image): void
    {
        $this->image = $image;
    }

    /**
     * @param string $availability
     */
    public function setAvailability(string $availability): void
    {
        $this->availability = $availability;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     */
    public function setSellingPrice(ProductPrice $sellingPrice): void
    {
        $this->sellingPrice = $sellingPrice;
    }

    /**
     * @param mixed $shortDescription
     */
    public function setShortDescription($shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @param int[] $flagIds
     */
    public function setFlagIds(array $flagIds): void
    {
        foreach ($flagIds as $flagId) {
            if (!is_int($flagId)) {
                throw new \InvalidArgumentException('Expected an array of integers.');
            }
        }
        $this->flagIds = $flagIds;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\View\ProductActionView $action
     */
    public function setAction(ProductActionView $action): void
    {
        $this->action = $action;
    }
}
