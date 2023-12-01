<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\ProductList;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductList\Exception\InvalidFindCriteriaForProductListUserError;

abstract class ProductListInputValidator implements ProductListInputValidatorInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @param array $input
     */
    public function validateInput(array $input): void
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $productListUuid = $input['uuid'];

        if ($customerUser === null && $productListUuid === null) {
            throw new InvalidFindCriteriaForProductListUserError('Either a product list UUID has to be provided, or the user has to be logged in to find a product list');
        }
    }
}
