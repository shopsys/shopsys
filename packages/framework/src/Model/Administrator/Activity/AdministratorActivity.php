<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'administrator_activities')]
#[ORM\Entity]
class AdministratorActivity
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'administrator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    protected $administrator;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 45)]
    protected $ipAddress;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $loginTime;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $lastActionTime;

    /**
     * @param string $ipAddress
     */
    public function __construct(
        Administrator $administrator,
        $ipAddress,
    ) {
        $this->administrator = $administrator;
        $this->ipAddress = $ipAddress;
        $this->loginTime = new DatePoint();
        $this->lastActionTime = new DatePoint();
    }

    public function updateLastActionTime(): void
    {
        $this->lastActionTime = new DatePoint();
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLoginTime()
    {
        return $this->loginTime;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastActionTime()
    {
        return $this->lastActionTime;
    }
}
