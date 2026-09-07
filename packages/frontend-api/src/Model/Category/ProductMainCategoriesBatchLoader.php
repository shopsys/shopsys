<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class ProductMainCategoriesBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly CategoryRepository $categoryRepository,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function loadByProductIds(array $productIds): Promise
    {
        $categoryIdsByProductId = $this->categoryRepository->getProductMainCategoryIdsIndexedByProductId(
            $productIds,
            $this->domain->getId(),
        );
        $categoryIds = array_map(
            static fn (int $productId): array => isset($categoryIdsByProductId[$productId]) ? [$categoryIdsByProductId[$productId]] : [],
            $productIds,
        );
        $categories = $this->categoryFacade->getVisibleCategoriesByIds($categoryIds, $this->domain->getCurrentDomainConfig());

        return $this->promiseAdapter->all(array_map(static fn (array $categories): ?Category => array_first($categories), $categories));
    }
}
