<?php

declare(strict_types = 1);

namespace App\Model\Gtm\Data;

use JsonSerializable;

class DataLayerSliderItem implements JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $link;

    /**
     * @var int|null
     */
    protected $position;

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
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
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
