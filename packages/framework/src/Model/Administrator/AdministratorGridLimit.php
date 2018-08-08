<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="administrator_grid_limits")
 */
class AdministratorGridLimit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator", inversedBy="gridLimits")
     * @ORM\JoinColumn(name="administrator_id", referencedColumnName="id", nullable=false)
     */
    protected $administrator;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=128)
     */
    protected $gridId;

    /**
     * @var int
     *
     * @ORM\Column(name="""limit""",type="integer")
     */
    protected $limit;

    public function __construct(Administrator $administrator, string $gridId, int $limit)
    {
        $this->administrator = $administrator;
        $this->gridId = $gridId;
        $this->limit = $limit;
    }

    public function getGridId(): string
    {
        return $this->gridId;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}
