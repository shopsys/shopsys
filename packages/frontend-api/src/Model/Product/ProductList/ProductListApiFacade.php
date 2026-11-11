<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductListApiFacade
{
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductListInputValidationFactory $productListInputValidationFactory,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly DataLoaderInterface $elasticsearchSearchBatchLoader,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
    ) {
    }

    /**
     * @param array{uuid:string|null, type:string} $input
     */
    public function findProductListByInputData(array $input): ?ProductList
    {
        $productListUuid = $input['uuid'];
        $productListType = $input['type'];
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $validator = $this->productListInputValidationFactory->createForProductListType($productListType);
        $validator->validateInput($input);

        if ($customerUser !== null) {
            return $this->productListFacade->findProductListByTypeAndCustomerUser($productListType, $customerUser, $productListUuid);
        }

        return $this->productListFacade->findAnonymousProductList($productListUuid, $productListType);
    }

    /**
     * @return \GraphQL\Executor\Promise\Promise Resolves to list<array{id: int, uuid: string}> in the stored list order
     */
    public function getVisibleProductIdentifiers(ProductList $productList): Promise
    {
        $productIds = $this->productListFacade->getProductIdsByProductList($productList);
        $queries = [];

        // Only project identities, in bounded searches; product documents are loaded after selecting the page.
        foreach (array_chunk($productIds, FilterQuery::DEFAULT_LIMIT) as $chunk) {
            $queries[] = $this->filterQueryFactory->createVisible()
                ->filterByProductIds($chunk)
                ->restrictFields([ProductExportFieldProvider::ID, ProductExportFieldProvider::UUID])
                ->setLimit(count($chunk))
                ->getQuery();
        }

        return $this->elasticsearchSearchBatchLoader->loadMany($queries)->then(
            fn (array $responses): array => $this->getOrderedProductIdentifiers($responses, $productIds),
        );
    }

    /**
     * @param array<int, array<string, mixed>> $responses
     * @param int[] $productIds
     * @return list<array{id: int, uuid: string}>
     */
    protected function getOrderedProductIdentifiers(array $responses, array $productIds): array
    {
        $identifiersById = [];

        foreach ($responses as $response) {
            $identifiersById += $this->productElasticsearchRepository->extractProductIdentifiersFromFields($response);
        }

        $ordered = [];

        foreach ($productIds as $id) {
            if (isset($identifiersById[$id])) {
                $ordered[] = $identifiersById[$id];
            }
        }

        return $ordered;
    }
}
