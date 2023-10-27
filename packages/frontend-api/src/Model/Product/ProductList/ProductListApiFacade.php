<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception\InvalidFindCriteriaForProductListUserError;

class ProductListApiFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param array{uuid:string|null, type:\Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum} $input
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList|null
     */
    public function findProductListByInputData(array $input): ?ProductList
    {
        $productListUuid = $input['uuid'];
        $productListType = $input['type'];
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $this->assertFilledCustomerUserOrUuid($customerUser, $productListUuid);

        if ($customerUser !== null) {
            return $this->productListFacade->findProductListByTypeAndCustomerUser($productListType, $customerUser, $productListUuid);
        }

        return $this->productListFacade->findAnonymousProductList($productListUuid, $productListType);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $productListUuid
     */
    protected function assertFilledCustomerUserOrUuid(?CustomerUser $customerUser, ?string $productListUuid): void
    {
        if ($customerUser === null && $productListUuid === null) {
            throw new InvalidFindCriteriaForProductListUserError('Either a product list UUID has to be provided, or the user has to be logged in to find a product list');
        }
    }
}
