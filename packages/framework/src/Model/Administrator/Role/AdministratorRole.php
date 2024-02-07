<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="administrator_roles")
 * @ORM\Entity
 */
class AdministratorRole
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator", inversedBy="roles")
     * @ORM\JoinColumn(nullable=false, name="administrator_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $administrator;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $role;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleData $administratorRoleData
     */
    public function __construct(AdministratorRoleData $administratorRoleData)
    {
        $this->administrator = $administratorRoleData->administrator;
        $this->role = $administratorRoleData->role;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}
