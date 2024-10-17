<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

class CategorySeoFiltersData
{
    /**
     * @var bool|null
     */
    public $useFlags;

    /**
     * @var bool|null
     */
    public $useOrdering;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public $parameters = [];

    public function __construct()
    {
        $this->useOrdering = true;
    }
}
