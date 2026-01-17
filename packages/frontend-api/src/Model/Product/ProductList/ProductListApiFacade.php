<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;

class ProductListApiFacade
{
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductListInputValidationFactory $productListInputValidationFactory,
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
}
