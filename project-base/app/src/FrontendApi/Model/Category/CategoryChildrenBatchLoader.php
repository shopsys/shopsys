<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoryChildrenBatchLoader
{
    /**
     * @var \GraphQL\Executor\Promise\PromiseAdapter
     */
    private PromiseAdapter $promiseAdapter;

    /**
     * @var \App\FrontendApi\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        PromiseAdapter $promiseAdapter,
        Domain $domain
    ) {
        $this->promiseAdapter = $promiseAdapter;
        $this->categoryFacade = $categoryFacade;
        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadByCategories(array $categories): Promise
    {
        $childrenByCategories = $this->categoryFacade->getAllVisibleChildrenByCategoriesAndDomainConfig(
            $categories,
            $this->domain->getCurrentDomainConfig()
        );

        return $this->promiseAdapter->all($childrenByCategories);
    }
}
