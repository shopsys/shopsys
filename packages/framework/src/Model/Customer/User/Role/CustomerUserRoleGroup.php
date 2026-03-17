<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @method \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation> getTranslations()
 */
#[ORM\Table(name: 'customer_user_role_groups')]
#[ORM\Entity]
class CustomerUserRoleGroup extends AbstractTranslatableEntity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation>
     */
    #[Prezent\Translations(targetEntity: CustomerUserRoleGroupTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    protected $roles;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    public function __construct(CustomerUserRoleGroupData $customerUserRoleGroupData)
    {
        $this->translations = new ArrayCollection();
        $this->uuid = $customerUserRoleGroupData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($customerUserRoleGroupData);
    }

    public function edit(CustomerUserRoleGroupData $customerUserRoleGroupData): void
    {
        $this->setData($customerUserRoleGroupData);
    }

    protected function setData(CustomerUserRoleGroupData $customerUserRoleGroupData): void
    {
        $this->roles = $customerUserRoleGroupData->roles;
        $this->setTranslations($customerUserRoleGroupData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new CustomerUserRoleGroupTranslation();
    }

    protected function setTranslations(CustomerUserRoleGroupData $customerUserRoleGroupData): void
    {
        foreach ($customerUserRoleGroupData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
