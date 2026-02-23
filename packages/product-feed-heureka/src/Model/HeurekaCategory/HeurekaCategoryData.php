<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

class HeurekaCategoryData
{
    /**
     * @var int|null
     */
    public $heurekaId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $fullName;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public $categories;

    /**
     * @var string
     */
    public $locale;

    public function __construct()
    {
        $this->categories = [];
    }
}
