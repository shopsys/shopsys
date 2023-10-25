<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception\CustomerUserNotLoggedUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception\InvalidFindCriteriaForProductListUserError;

class ProductListQuery extends AbstractQuery
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
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList|null
     */
    public function productListQuery(Argument $argument): ?ProductList
    {
        $input = $argument['input'];
        $productListUuid = $input['uuid'];
        $productListType = $input['type'];
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $this->assertFilledCustomerUserOrUuid($customerUser, $productListUuid);

        if ($customerUser !== null) {
            return $this->productListFacade->findProductListByTypeAndCustomerUser($productListType, $customerUser, $productListUuid);
        }

        return $this->productListFacade->findAnonymousProductListByTypeAndUuid($productListType, $productListUuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListTypeEnum
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList[]
     */
    public function productListsByTypeQuery(ProductListTypeEnum $productListTypeEnum): array
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null) {
            throw new CustomerUserNotLoggedUserError('You have to be logged in to get your product lists by type');
        }

        return $this->productListFacade->getProductListsByTypeAndCustomerUser($productListTypeEnum, $customerUser);
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
