<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesSearchResolver as BaseCategoriesSearchResolver;

/**
 * @property \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade)
 */
class CategoriesSearchResolver extends BaseCategoriesSearchResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolveSearch(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        return parent::resolveSearch($argument);
    }
}
