<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

class CustomerUserLoginTypeData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public $customerUser;

    /**
     * @var string
     */
    public $loginType;

    /**
     * @var \DateTime
     */
    public $lastLoggedInAt;

    /**
     * @var string|null
     */
    public $externalId;
}
