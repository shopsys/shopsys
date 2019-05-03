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
     * @param int $id
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Component\Image\View\ImageView|null $image
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param string|null $shortDescription
     * @param int[] $flagIds
     * @param \Shopsys\FrameworkBundle\Model\Product\View\ProductActionView $action
     */
    public function __construct(
        int $id,
        string $name,
        ?ImageView $image,
        string $availability,
        ProductPrice $sellingPrice,
        ?string $shortDescription,
        array $flagIds,
        ProductActionView $action
    ) {
        foreach ($flagIds as $flagId) {
            if (!is_int($flagId)) {
                throw new \InvalidArgumentException('Expected an array of integers.');
            }
        }

        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->availability = $availability;
        $this->sellingPrice = $sellingPrice;
        $this->shortDescription = $shortDescription;
        $this->flagIds = $flagIds;
        $this->action = $action;
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
}
