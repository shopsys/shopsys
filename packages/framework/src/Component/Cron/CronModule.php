<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cron_modules")
 * @ORM\Entity
 */
class CronModule
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @ORM\Id
     */
    protected $serviceId;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $scheduled;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $suspended;

    /**
     * @param string $serviceId
     */
    public function __construct($serviceId)
    {
        $this->serviceId = $serviceId;
        $this->scheduled = false;
        $this->suspended = false;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function isScheduled()
    {
        return $this->scheduled;
    }

    public function isSuspended()
    {
        return $this->suspended;
    }

    public function schedule()
    {
        $this->scheduled = true;
    }

    public function unschedule()
    {
        $this->scheduled = false;
        $this->suspended = false;
    }

    public function suspend()
    {
        $this->suspended = true;
    }
}
