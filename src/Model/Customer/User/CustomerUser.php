<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser as BaseUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseUserData;

/**
 * @ORM\Table(
 *     name="customer_users",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="email_domain", columns={"email", "domain_id"})
 *     },
 *     indexes={
 *         @ORM\Index(columns={"email"})
 *     }
 * )
 * @ORM\Entity
 */
class CustomerUser extends BaseUser
{

    /**
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    protected $gender;

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    public function __construct(
        BaseUserData $customerUserData,
        ?DeliveryAddress $deliveryAddress
    ) {
        parent::__construct($customerUserData, $deliveryAddress);
        $this->gender = $customerUserData->gender;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function edit(BaseUserData $customerUserData)
    {
        parent::edit($customerUserData);
        $this->gender = $customerUserData->gender;
    }

    /**
     * @return string
     */
    public function getGender(): string{
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender(string $gender): void{
        $this->gender = $gender;
    }


}
