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

    public function __construct(string $serviceId)
    {
        $this->serviceId = $serviceId;
        $this->scheduled = false;
        $this->suspended = false;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function isScheduled(): bool
    {
        return $this->scheduled;
    }

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function schedule(): void
    {
        $this->scheduled = true;
    }

    public function unschedule(): void
    {
        $this->scheduled = false;
        $this->suspended = false;
    }

    public function suspend(): void
    {
        $this->suspended = true;
    }
}
