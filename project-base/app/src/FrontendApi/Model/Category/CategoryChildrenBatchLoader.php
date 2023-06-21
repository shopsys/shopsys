<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoryChildrenBatchLoader
{
    /**
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private CategoryFacade $categoryFacade,
        private PromiseAdapter $promiseAdapter,
        private Domain $domain,
    ) {
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByCategories(array $categories): Promise
    {
        $childrenByCategories = $this->categoryFacade->getAllVisibleChildrenByCategoriesAndDomainConfig(
            $categories,
            $this->domain->getCurrentDomainConfig(),
        );

        return $this->promiseAdapter->all($childrenByCategories);
    }
}
