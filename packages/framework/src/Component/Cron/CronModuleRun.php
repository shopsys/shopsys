<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'cron_module_runs')]
#[ORM\Index(columns: ['started_at'])]
#[ORM\Index(columns: ['finished_at'])]
#[ORM\Entity]
class CronModuleRun
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Cron\CronModule
     */
    #[ORM\JoinColumn(name: 'cron_module_id', referencedColumnName: 'service_id', nullable: false, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: CronModule::class)]
    protected $cronModule;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $status;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $startedAt;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $finishedAt;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    protected $duration;

    public function __construct(
        CronModule $cronModule,
        string $status,
        DateTimeImmutable $startedAt,
        DateTimeImmutable $finishedAt,
        int $duration,
    ) {
        $this->cronModule = $cronModule;
        $this->status = $status;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
        $this->duration = $duration;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule
     */
    public function getCronModule()
    {
        return $this->cronModule;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
