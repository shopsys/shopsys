<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'administrator_grid_limits')]
#[ORM\Entity]
class AdministratorGridLimit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'administrator_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Administrator::class, inversedBy: 'gridLimits')]
    protected $administrator;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 128)]
    protected $gridId;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(name: '"limit"', type: 'integer')]
    protected $limit;

    /**
     * @param string $gridId
     * @param int $limit
     */
    public function __construct(Administrator $administrator, $gridId, $limit)
    {
        $this->administrator = $administrator;
        $this->gridId = $gridId;
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function getGridId()
    {
        return $this->gridId;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }
}
