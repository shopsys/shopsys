<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\List;

class ProductListData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum
     */
    public $type;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public $customerUser;

    /**
     * @var string|null
     */
    public $uuid;
}
