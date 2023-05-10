<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use App\Model\Administrator\Administrator;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain as BaseCustomerUserRefreshTokenChain;

/**
 * @ORM\Entity
 * @ORM\Table(name="customer_user_refresh_token_chain")
 * @property \App\Model\Customer\User\CustomerUser $customerUser
 */
class CustomerUserRefreshTokenChain extends BaseCustomerUserRefreshTokenChain
{
    /**
     * @var \App\Model\Administrator\Administrator|null
     * @ORM\ManyToOne(targetEntity="App\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private ?Administrator $administrator;

    /**
     * @param \App\Model\Administrator\Administrator|null $administrator
     */
    public function setAdministrator(?Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }

    /**
     * @return \App\Model\Administrator\Administrator|null
     */
    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }
}
