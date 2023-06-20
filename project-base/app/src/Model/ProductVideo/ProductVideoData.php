<?php

declare(strict_types=1);

namespace App\Model\ProductVideo;

class ProductVideoData
{
    public ?int $id = null;

    public string $videoToken;

    public array $videoTokenDescriptions = [];
}
