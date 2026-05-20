<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Role;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'administrator_roles')]
#[ORM\Entity]
class AdministratorRole
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'administrator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Administrator::class, inversedBy: 'roles')]
    protected $administrator;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    protected $role;

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
