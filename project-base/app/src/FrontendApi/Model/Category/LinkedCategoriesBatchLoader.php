<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class LinkedCategoriesBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        private PromiseAdapter $promiseAdapter,
        private Domain $domain,
        private CategoryFacade $categoryFacade,
    ) {
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByCategories(array $categories): Promise
    {
        return $this->promiseAdapter->all($this->categoryFacade->getVisibleLinkedCategories($categories, $this->domain->getCurrentDomainConfig()));
    }
}
