<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoriesBatchLoader
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
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\FrontendApi\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(PromiseAdapter $promiseAdapter, CategoryFacade $categoryFacade, Domain $domain)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->categoryFacade = $categoryFacade;
        $this->domain = $domain;
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
