<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string|null
     */
    public $rgbColor;

    /**
     * @var bool
     */
    public $visible;

    /**
     * @var string|null
     */
    public ?string $uuid = null;

    public function __construct()
    {
        $this->name = [];
        $this->visible = false;
    }
}
