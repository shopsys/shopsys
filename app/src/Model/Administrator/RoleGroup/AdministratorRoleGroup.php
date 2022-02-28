<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Security\Roles;

/**
 * @ORM\Entity
 * @ORM\Table(name="administrator_role_groups")
 */
class AdministratorRoleGroup
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100, unique = true)
     */
    private string $name;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    private array $roles;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    public function __construct(AdministratorRoleGroupData $administratorRoleGroupData)
    {
        $this->roles = [];
        $this->setData($administratorRoleGroupData);
    }

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    public function edit(AdministratorRoleGroupData $administratorRoleGroupData): void
    {
        $this->setData($administratorRoleGroupData);
    }

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    private function setData(AdministratorRoleGroupData $administratorRoleGroupData): void
    {
        $this->name = $administratorRoleGroupData->name;
        $this->roles = $administratorRoleGroupData->roles;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = Roles::ROLE_ADMIN;

        return array_unique($roles);
    }
}
