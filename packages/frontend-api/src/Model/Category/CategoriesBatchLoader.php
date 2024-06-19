<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoriesBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int[][] $categoriesIds
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByIds(array $categoriesIds): Promise
    {
        return $this->promiseAdapter->all($this->categoryFacade->getVisibleCategoriesByIds($categoriesIds, $this->domain->getCurrentDomainConfig()));
    }
}
