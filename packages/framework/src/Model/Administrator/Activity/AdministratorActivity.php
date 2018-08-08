<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use DateTime;
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
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Administrator\Administrator")
     * @ORM\JoinColumn(nullable=false, name="administrator_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $administrator;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=45)
     */
    protected $ipAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $loginTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $lastActionTime;

    /**
     * @param string $ipAddress
     */
    public function __construct(
        Administrator $administrator,
        $ipAddress
    ) {
        $this->administrator = $administrator;
        $this->ipAddress = $ipAddress;
        $this->loginTime = new DateTime();
        $this->lastActionTime = new DateTime();
    }

    public function updateLastActionTime()
    {
        $this->lastActionTime = new DateTime();
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getLoginTime(): \DateTime
    {
        return $this->loginTime;
    }

    public function getLastActionTime(): \DateTime
    {
        return $this->lastActionTime;
    }
}
