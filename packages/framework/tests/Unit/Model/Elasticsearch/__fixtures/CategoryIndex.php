<?php

namespace Tests\FrameworkBundle\Unit\Model\Elasticsearch\__fixtures;

use Shopsys\FrameworkBundle\Model\Elasticsearch\AbstractIndex;

class CategoryIndex extends AbstractIndex
{
    public function getName(): string
    {
        return 'category';
    }
}
