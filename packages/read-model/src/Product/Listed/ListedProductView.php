<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Webmozart\Assert\Assert;

class ListedProductView
{
    /**
     * ListedProductView constructor.
     *
     * @param int $id
     * @param string $name
     * @param string|null $shortDescription
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param int[] $flagIds
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $action
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $image
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly ?string $shortDescription = null,
        protected readonly string $availability,
        protected readonly ProductPrice $sellingPrice,
        protected readonly array $flagIds = [],
        protected readonly ProductActionView $action,
        protected readonly ?ImageView $image = null
    ) {
        Assert::allInteger($flagIds);
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
     * @return \Shopsys\ReadModelBundle\Image\ImageView|null
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
     * @return string|null
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
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionView
     */
    public function getAction(): ProductActionView
    {
        return $this->action;
    }
}
