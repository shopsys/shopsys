<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList;

use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\List\Exception\ProductAlreadyInListException;
use Shopsys\FrameworkBundle\Model\Product\List\Exception\ProductNotInListException;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListDataFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductAlreadyInListUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductListNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductNotInListUserError;
use Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class ProductListMutation extends AbstractMutation
{
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductListDataFactory $productListDataFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductListApiFacade $productListApiFacade,
    ) {
    }

    public function addProductToListMutation(Argument $argument): ProductList
    {
        $input = $argument['input'];
        $productUuid = $input['productUuid'];
        $productListInput = $input['productListInput'];
        $productListType = $productListInput['type'];
        $productListUuid = $productListInput['uuid'];
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $productList = null;

        if ($customerUser !== null) {
            $productList = $this->productListFacade->findProductListByTypeAndCustomerUser($productListType, $customerUser, $productListUuid);
        } elseif ($productListUuid !== null) {
            $productList = $this->productListFacade->findAnonymousProductList($productListUuid, $productListType);
        }

        if ($productList === null) {
            if ($productListUuid !== null && $this->productListFacade->existsProductListWithUuid($productListUuid)) {
                $productListUuid = Uuid::uuid4()->toString();
            }

            $productListData = $this->productListDataFactory->create($productListType, $customerUser, $productListUuid);
            $productList = $this->productListFacade->create($productListData);
        }

        try {
            $product = $this->productFacade->getByUuid($productUuid);
        } catch (ProductNotFoundException $exception) {
            throw new ProductNotFoundUserError(sprintf('Product with UUID "%s" not found', $productUuid));
        }

        try {
            return $this->productListFacade->addProductToList($productList, $product);
        } catch (ProductAlreadyInListException $exception) {
            throw new ProductAlreadyInListUserError($exception->getMessage(), $productListType);
        }
    }

    public function removeProductFromListMutation(Argument $argument): ?ProductList
    {
        $input = $argument['input'];
        $productListInput = $input['productListInput'];
        $productListType = $productListInput['type'];
        $productList = $this->productListApiFacade->findProductListByInputData($productListInput);

        if ($productList === null) {
            throw new ProductListNotFoundUserError('Product list not found', $productListType);
        }

        $productUuid = $input['productUuid'];

        try {
            $product = $this->productFacade->getByUuid($productUuid);
        } catch (ProductNotFoundException $exception) {
            throw new ProductNotFoundUserError(sprintf('Product with UUID "%s" not found', $productUuid));
        }

        try {
            return $this->productListFacade->removeProductFromList($productList, $product);
        } catch (ProductNotInListException $exception) {
            throw new ProductNotInListUserError($exception->getMessage(), $productListType);
        }
    }

    public function removeProductListMutation(Argument $argument): ?ProductList
    {
        $input = $argument['input'];
        $productListType = $input['type'];
        $productList = $this->productListApiFacade->findProductListByInputData($input);

        if ($productList === null) {
            throw new ProductListNotFoundUserError('Product list not found', $productListType);
        }

        $this->productListFacade->removeProductList($productList);

        return null;
    }
}
