<?php

declare(strict_types=1);

namespace App\Model\ProductVideo;

class ProductVideoData
{
    public ?int $id = null;

    public string $videoToken;

    /**
     * @var mixed[]
     */
    public array $videoTokenDescriptions = [];
}
