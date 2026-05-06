<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

class ZboziCategoryData
{
    /**
     * @var int|null
     */
    public $zboziId;

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
