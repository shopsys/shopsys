<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

class ProductVideoData
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $videoToken;

    /**
     * @var string[]
     */
    public $videoTokenDescriptions = [];
}
