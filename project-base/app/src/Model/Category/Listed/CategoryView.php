<?php

declare(strict_types=1);

namespace App\Model\Category\Listed;

class CategoryView
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $url;

    /**
     * @param string $name
     * @param string $url
     */
    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
    }
}
