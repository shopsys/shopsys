<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoriesSearchQuery as BaseCategoriesSearchQuery;

/**
 * @property \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
 */
class CategoriesSearchQuery extends BaseCategoriesSearchQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(Domain $domain, CategoryFacade $categoryFacade)
    {
        parent::__construct($domain, $categoryFacade);
    }

    /**
     * {@inheritdoc}
     */
    public function categoriesSearchQuery(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        return parent::categoriesSearchQuery($argument);
    }
}
