<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *      name="customer_user_login_types",
 *     indexes={
 *          @ORM\Index(columns={"customer_user_id", "login_type"})
 *      }
 *  )
 * @ORM\Entity
 */
class CustomerUserLoginType
{
    public const string TYPE_WEB = 'web';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $customerUser;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @ORM\Id
     */
    protected $loginType;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $lastLoggedInAt;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $externalId;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData $customerUserLoginTypeData
     */
    public function __construct(
        CustomerUserLoginTypeData $customerUserLoginTypeData,
    ) {
        $this->customerUser = $customerUserLoginTypeData->customerUser;
        $this->loginType = $customerUserLoginTypeData->loginType;
        $this->externalId = $customerUserLoginTypeData->externalId;
        $this->setLastLoggedNow();
    }

    public function setLastLoggedNow(): void
    {
        $this->lastLoggedInAt = new DateTime();
    }
}
