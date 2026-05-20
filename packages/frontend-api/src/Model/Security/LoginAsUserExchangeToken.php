<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable(exposed: false)]
#[ORM\Table(name: 'login_as_user_exchange_tokens')]
#[ORM\Entity]
class LoginAsUserExchangeToken
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[ORM\Id]
    protected $token;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    #[ORM\JoinColumn(nullable: false, name: 'customer_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    #[ORM\JoinColumn(nullable: false, name: 'administrator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    protected $administrator;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $expiresAt;

    public function __construct(
        string $token,
        CustomerUser $customerUser,
        Administrator $administrator,
        DateTimeImmutable $expiresAt,
    ) {
        $this->token = $token;
        $this->customerUser = $customerUser;
        $this->administrator = $administrator;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }
}
