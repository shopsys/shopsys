<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Doctrine\ORM\Mapping as ORM;
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
    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    protected $gender;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $newsletterSubscription;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true, unique=true)
     */
    protected $erpCustomerNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lastName;

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function __construct(
        BaseUserData $customerUserData
    ) {
        parent::__construct($customerUserData);
        $this->gender = $customerUserData->gender;
        $this->newsletterSubscription = $customerUserData->newsletterSubscription;
        $this->erpCustomerNumber = $customerUserData->erpCustomerNumber;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function edit(BaseUserData $customerUserData)
    {
        parent::edit($customerUserData);
        $this->gender = $customerUserData->gender;
        $this->newsletterSubscription = $customerUserData->newsletterSubscription;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @return bool
     */
    public function isNewsletterSubscription(): bool
    {
        return $this->newsletterSubscription;
    }

    /**
     * @return array
     */
    public static function getAllGenders(): array
    {
        return [
            self::GENDER_MALE => t('pan'),
            self::GENDER_FEMALE => t('paní/slečna'),
        ];
    }

    /**
     * @return int|null
     */
    public function getErpCustomerNumber(): ?int
    {
        return $this->erpCustomerNumber;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return parent::getLastName();
    }

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return parent::getFullName();
    }
}
