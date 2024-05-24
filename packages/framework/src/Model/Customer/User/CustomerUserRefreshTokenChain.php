<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="customer_user_refresh_token_chain")
 * @ORM\Entity
 */
class CustomerUserRefreshTokenChain
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser", inversedBy="refreshTokenChain")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $customerUser;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $tokenChain;

    /**
     * @var string
     * @ORM\Column(type="guid", nullable=false)
     */
    protected $deviceId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $expiredAt;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $administrator;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData
     */
    public function __construct(CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData)
    {
        $this->uuid = $customerUserRefreshTokenChainData->uuid ?: Uuid::uuid4()->toString();
        $this->customerUser = $customerUserRefreshTokenChainData->customerUser;
        $this->tokenChain = $customerUserRefreshTokenChainData->tokenChain;
        $this->deviceId = $customerUserRefreshTokenChainData->deviceId;
        $this->expiredAt = $customerUserRefreshTokenChainData->expiredAt;
        $this->administrator = $customerUserRefreshTokenChainData->administrator;
    }

    /**
     * @return string
     */
    public function getTokenChain()
    {
        return $this->tokenChain;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }
}
