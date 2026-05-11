<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class CategoriesIdsToCategoriesTransformer extends IdsToEntitiesTransformer
{
    public function __construct(CategoryFacade $categoryFacade)
    {
        parent::__construct($categoryFacade);
    }
}
