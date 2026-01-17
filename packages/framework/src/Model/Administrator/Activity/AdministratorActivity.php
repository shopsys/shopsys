<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\Clock\DatePoint;

/**
 * @ORM\Table(name="administrator_activities")
 * @ORM\Entity
 */
class AdministratorActivity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=false, name="administrator_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $administrator;

    /**
     * @var string
     * @ORM\Column(type="string", length=45)
     */
    protected $ipAddress;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    protected $loginTime;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
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
