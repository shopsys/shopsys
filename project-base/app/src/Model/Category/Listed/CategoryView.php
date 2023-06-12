<?php

declare(strict_types=1);

namespace App\Model\Category\Listed;

class CategoryView
{
    /**
     * @param string $name
     * @param string $url
     */
    public function __construct(public string $name, public string $url)
    {
    }
}
