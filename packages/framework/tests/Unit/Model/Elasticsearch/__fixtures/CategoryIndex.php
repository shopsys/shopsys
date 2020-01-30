<?php

namespace Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class CategoryIndex extends AbstractIndex
{
    /**
     * @param \Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures\CategoryDataProvider $dataProvider
     */
    public function __construct(CategoryDataProvider $dataProvider)
    {
        parent::__construct($dataProvider);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'category';
    }
}
