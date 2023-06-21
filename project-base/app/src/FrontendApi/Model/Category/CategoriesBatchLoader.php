<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoriesBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private PromiseAdapter $promiseAdapter,
        private CategoryFacade $categoryFacade,
        private Domain $domain,
    ) {
    }

    /**
     * @param int[][] $categoriesIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByIds(array $categoriesIds): Promise
    {
        return $this->promiseAdapter->all($this->categoryFacade->getCategoriesByIds($categoriesIds, $this->domain->getCurrentDomainConfig()));
    }
}
