<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;

class ProductListApiFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListInputValidationFactory $productListInputValidationFactory
     */
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductListInputValidationFactory $productListInputValidationFactory,
    ) {
    }

    /**
     * @param array{uuid:string|null, type:string} $input
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList|null
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
