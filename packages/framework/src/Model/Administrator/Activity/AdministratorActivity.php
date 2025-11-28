<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

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
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $ipAddress
     */
    public function __construct(
        Administrator $administrator,
        $ipAddress,
    ) {
        $this->administrator = $administrator;
        $this->ipAddress = $ipAddress;
        $this->loginTime = new DateTimeImmutable();
        $this->lastActionTime = new DateTimeImmutable();
    }

    public function updateLastActionTime()
    {
        $this->lastActionTime = new DateTimeImmutable();
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
