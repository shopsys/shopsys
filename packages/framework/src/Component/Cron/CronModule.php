<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
#[ORM\Table(name: 'cron_modules')]
#[ORM\Entity]
class CronModule
{
    public const string CRON_STATUS_OK = 'ok';
    public const string CRON_STATUS_ERROR = 'error';
    public const string CRON_STATUS_RUNNING = 'running';

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    #[ORM\Id]
    protected $serviceId;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $scheduled;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $suspended;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $enabled;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string')]
    protected $status;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $lastStartedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $lastFinishedAt;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
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
        $this->scheduled = true;
        $this->suspended = true;
    }

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
        $this->lastStartedAt = new DatePoint();
    }

    public function updateLastFinishedAt(): void
    {
        $this->lastFinishedAt = new DatePoint();
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastStartedAt()
    {
        return $this->lastStartedAt;
    }

    /**
     * @return \DateTimeImmutable|null
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
    public function setLastDuration($duration): void
    {
        $this->lastDuration = $duration;
    }
}
