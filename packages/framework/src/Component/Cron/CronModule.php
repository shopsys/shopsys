<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cron_modules")
 * @ORM\Entity
 */
class CronModule
{
    public const CRON_STATUS_OK = 'ok';
    public const CRON_STATUS_ERROR = 'error';
    public const CRON_STATUS_RUNNING = 'running';

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @ORM\Id
     */
    protected $serviceId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $scheduled;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $suspended;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastStartedAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastFinishedAt;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $lastDuration;

    /**
     * @param string $serviceId
     */
    public function __construct($serviceId)
    {
        $this->serviceId = $serviceId;
        $this->scheduled = false;
        $this->suspended = false;
        $this->status = self::CRON_STATUS_OK;
        $this->enabled = true;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @return bool
     */
    public function isScheduled()
    {
        return $this->scheduled;
    }

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function setStatusFailed(): void
    {
        $this->status = self::CRON_STATUS_ERROR;
    }

    public function setStatusOk(): void
    {
        $this->status = self::CRON_STATUS_OK;
    }

    public function setStatusRunning(): void
    {
        $this->status = self::CRON_STATUS_RUNNING;
    }

    public function updateLastStartedAt(): void
    {
        $this->lastStartedAt = new DateTime();
    }

    public function updateLastFinishedAt(): void
    {
        $this->lastFinishedAt = new DateTime();
    }

    /**
     * @return \DateTime|null
     */
    public function getLastStartedAt()
    {
        return $this->lastStartedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastFinishedAt()
    {
        return $this->lastFinishedAt;
    }

    /**
     * @return int|null
     */
    public function getLastDuration()
    {
        return $this->lastDuration;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $duration
     */
    public function setLastDuration(int $duration): void
    {
        $this->lastDuration = $duration;
    }
}
