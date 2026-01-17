<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception\CustomerUserNotLoggedUserError;

class ProductListQuery extends AbstractQuery
{
    public function __construct(
        protected readonly ProductListFacade $productListFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductListApiFacade $productListApiFacade,
    ) {
    }

    public function productListQuery(Argument $argument): ?ProductList
    {
        $input = $argument['input'];

        return $this->productListApiFacade->findProductListByInputData($input);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductList[]
     */
    public function productListsByTypeQuery(string $productListType): array
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null) {
            throw new CustomerUserNotLoggedUserError('You have to be logged in to get your product lists by type');
        }

        return $this->productListFacade->getProductListsByTypeAndCustomerUser($productListType, $customerUser);
    }
}
