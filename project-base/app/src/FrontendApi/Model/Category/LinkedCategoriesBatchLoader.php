<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class LinkedCategoriesBatchLoader
{
    /**
     * @var \GraphQL\Executor\Promise\PromiseAdapter
     */
    private PromiseAdapter $promiseAdapter;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\FrontendApi\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        PromiseAdapter $promiseAdapter,
        Domain $domain,
        CategoryFacade $categoryFacade
    ) {
        $this->promiseAdapter = $promiseAdapter;
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
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
