<?php

declare(strict_types = 1);

namespace App\Model\Gtm\Data;

use JsonSerializable;

class DataLayerProduct implements JsonSerializable
{
    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $price;

    /**
     * @var string|null
     */
    protected $tax;

    /**
     * @var string|null
     */
    protected $priceWithTax;

    /**
     * @var string|null
     */
    protected $category;

    /**
     * @var string|null
     */
    protected $variant;

    /**
     * @var string|null
     */
    protected $availability;

    /**
     * @var string[]|null
     */
    protected $tags;

    /**
     * @var int|null
     */
    protected $quantity;

    /**
     * @var string|null
     */
    protected $list;

    /**
     * @var int|null
     */
    protected $position;

    /**
     * @param string $id
     */
    public function setId(string $id): void
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
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    /**
     * @param string $tax
     */
    public function setTax(string $tax): void
    {
        $this->tax = $tax;
    }

    /**
     * @param string $priceWithTax
     */
    public function setPriceWithTax(string $priceWithTax): void
    {
        $this->priceWithTax = $priceWithTax;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @param string $variant
     */
    public function setVariant(string $variant): void
    {
        $this->variant = $variant;
    }

    /**
     * @param string $availability
     */
    public function setAvailability(string $availability): void
    {
        $this->availability = $availability;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @param string $list
     */
    public function setList(string $list): void
    {
        $this->list = $list;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_filter(get_object_vars($this));
    }
}
