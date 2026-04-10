<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain as BaseCustomerUserRefreshTokenChain;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @property \App\Model\Customer\User\CustomerUser $customerUser
 * @property \App\Model\Administrator\Administrator|null $administrator
 * @method \App\Model\Administrator\Administrator|null getAdministrator()
 */
#[AsMcpTable(false)]
#[ORM\Table(name: 'customer_user_refresh_token_chain')]
#[ORM\Entity]
class CustomerUserRefreshTokenChain extends BaseCustomerUserRefreshTokenChain
{
}
