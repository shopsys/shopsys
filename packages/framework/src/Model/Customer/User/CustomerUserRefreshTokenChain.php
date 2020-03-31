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
     *
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser", inversedBy="refreshTokenChain")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $customerUser;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $tokenChain;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $expiredAt;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData
     */
    public function __construct(CustomerUserRefreshTokenChainData $customerUserRefreshTokenChainData)
    {
        $this->uuid = $customerUserRefreshTokenChainData->uuid ?: Uuid::uuid4()->toString();
        $this->customerUser = $customerUserRefreshTokenChainData->customerUser;
        $this->tokenChain = $customerUserRefreshTokenChainData->tokenChain;
        $this->expiredAt = $customerUserRefreshTokenChainData->expiredAt;
    }

    /**
     * @return string
     */
    public function getTokenChain(): string
    {
        return $this->tokenChain;
    }
}
