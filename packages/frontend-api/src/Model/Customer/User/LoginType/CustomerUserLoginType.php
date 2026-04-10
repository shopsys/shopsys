<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'customer_user_login_types')]
#[ORM\Index(columns: ['customer_user_id', 'login_type'])]
#[ORM\Entity]
class CustomerUserLoginType
{
    public const string TYPE_WEB = 'web';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CustomerUser::class)]
    protected $customerUser;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    #[ORM\Id]
    protected $loginType;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $lastLoggedInAt;

    /**
     * @var string|null
     */
    #[AsMcpColumn(false)]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $externalId;

    public function __construct(
        CustomerUserLoginTypeData $customerUserLoginTypeData,
    ) {
        $this->customerUser = $customerUserLoginTypeData->customerUser;
        $this->loginType = $customerUserLoginTypeData->loginType;
        $this->externalId = $customerUserLoginTypeData->externalId;
        $this->lastLoggedInAt = $customerUserLoginTypeData->lastLoggedInAt;
    }

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function setLastLoggedInAt($dateTime): void
    {
        $this->lastLoggedInAt = $dateTime;
    }

    /**
     * @return string
     */
    public function getLoginType()
    {
        return $this->loginType;
    }

    /**
     * @return string|null
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastLoggedInAt()
    {
        return $this->lastLoggedInAt;
    }
}
