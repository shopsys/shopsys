<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

class AutocompleteFavoriteData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public $products = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public $categories = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public $brands = [];
}
