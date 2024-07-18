<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Security\Roles;

/**
 * @ORM\Entity
 * @ORM\Table(name="administrator_role_groups")
 */
class AdministratorRoleGroup
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, unique = true)
     */
    protected $name;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    protected $roles;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    public function __construct(AdministratorRoleGroupData $administratorRoleGroupData)
    {
        $this->roles = [];
        $this->setData($administratorRoleGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    public function edit(AdministratorRoleGroupData $administratorRoleGroupData): void
    {
        $this->setData($administratorRoleGroupData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    protected function setData(AdministratorRoleGroupData $administratorRoleGroupData): void
    {
        $this->name = $administratorRoleGroupData->name;
        $this->roles = $administratorRoleGroupData->roles;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = Roles::ROLE_ADMIN;

        return array_unique($roles);
    }
}
