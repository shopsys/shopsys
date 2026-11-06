<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductListApiFacade
{
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductListInputValidationFactory $productListInputValidationFactory,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly DataLoaderInterface $elasticsearchSearchBatchLoader,
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

    public function getVisibleProductIdentifiers(ProductList $productList): Promise
    {
        $productIds = $this->productListFacade->getProductIdsByProductList($productList);
        $queries = [];

        // Only project identities, in bounded searches; product documents are loaded after selecting the page.
        foreach (array_chunk($productIds, 1000) as $chunk) {
            $queries[] = $this->filterQueryFactory->createVisible()
                ->filterByProductIds($chunk)
                ->restrictFields(['id', 'uuid'])
                ->setLimit(count($chunk))
                ->getQuery();
        }

        return $this->elasticsearchSearchBatchLoader->loadMany($queries)->then(static function (array $responses) use ($productIds): array {
            $identifiersById = [];

            foreach ($responses as $response) {
                foreach ($response['hits']['hits'] as $hit) {
                    $id = (int)$hit['fields']['id'][0];
                    $identifiersById[$id] = ['id' => $id, 'uuid' => $hit['fields']['uuid'][0]];
                }
            }

            $ordered = [];

            foreach ($productIds as $id) {
                if (isset($identifiersById[$id])) {
                    $ordered[] = $identifiersById[$id];
                }
            }

            return $ordered;
        });
    }
}
