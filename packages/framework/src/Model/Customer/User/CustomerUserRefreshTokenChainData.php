<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

class CustomerUserRefreshTokenChainData
{
    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public $customerUser;

    /**
     * @var string|null
     */
    public $tokenChain;

    /**
     * @var \DateTime|null
     */
    public $expiredAt;
}
