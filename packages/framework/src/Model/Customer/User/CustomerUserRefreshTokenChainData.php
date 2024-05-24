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
     * @var string|null
     */
    public $deviceId;

    /**
     * @var \DateTime|null
     */
    public $expiredAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public $administrator;
}
