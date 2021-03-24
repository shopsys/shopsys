<?php

declare(strict_types=1);

namespace App\Model\Product\Detail;

class ProductFileView
{
    /**
     * @var string
     */
    public string $anchorText;

    /**
     * @var string
     */
    public string $url;

    /**
     * @param string $anchorText
     * @param string $url
     */
    public function __construct(string $anchorText, string $url)
    {
        $this->anchorText = $anchorText;
        $this->url = $url;
    }
}
