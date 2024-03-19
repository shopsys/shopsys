<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class ProductListDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductListData
     */
    protected function createInstance(): ProductListData
    {
        return new ProductListData();
    }

    /**
     * @param string $productListType
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\List\ProductListData
     */
    public function create(
        string $productListType,
        ?CustomerUser $customerUser,
        ?string $uuid,
    ): ProductListData {
        $productListData = $this->createInstance();
        $productListData->type = $productListType;
        $productListData->customerUser = $customerUser;
        $productListData->uuid = $uuid;

        return $productListData;
    }
}
