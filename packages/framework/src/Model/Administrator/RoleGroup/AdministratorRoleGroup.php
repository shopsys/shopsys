<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

#[ORM\Table(name: 'administrator_role_groups')]
#[ORM\Entity]
class AdministratorRoleGroup
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    protected $name;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $systemManaged = false;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    protected $roles;

    public function __construct(AdministratorRoleGroupData $administratorRoleGroupData)
    {
        $this->roles = [];
        $this->setData($administratorRoleGroupData);
    }

    public function edit(AdministratorRoleGroupData $administratorRoleGroupData): void
    {
        $this->setData($administratorRoleGroupData);
    }

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
        return match ($this->name) {
            SystemRole::ALL => t('Full access'),
            SystemRole::ALL_VIEW => t('Full access (view only)'),
            default => $this->name,
        };
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = SystemRole::ADMIN;

        return array_unique($roles);
    }

    /**
     * Check if this role group can be edited by users
     *
     * System-managed roles are created and maintained by the application
     * and should not be modified by end users
     */
    public function isSystemManaged(): bool
    {
        return $this->systemManaged;
    }
}
