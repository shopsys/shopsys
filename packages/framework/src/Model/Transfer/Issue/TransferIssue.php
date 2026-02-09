<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;
use Symfony\Component\Clock\DatePoint;

#[ORM\Table(name: 'transfer_issues')]
#[ORM\Index(columns: ['created_at', 'deleted_at', 'transfer_id'])]
#[ORM\Entity]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class TransferIssue
{
    public const string SEVERITY_ERROR = 'error';
    public const string SEVERITY_WARNING = 'warning';
    public const string SEVERITY_CRITICAL = 'critical';

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    #[ORM\JoinColumn(name: 'transfer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Transfer::class)]
    protected $transfer;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    protected $severity;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: false)]
    protected $message;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    protected $deletedAt;

    public function __construct(Transfer $transfer, TransferIssueData $transferIssueData)
    {
        $this->createdAt = new DatePoint();
        $this->transfer = $transfer;
        $this->severity = $transferIssueData->severity;
        $this->message = $transferIssueData->message;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    public function getTransfer()
    {
        return $this->transfer;
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function setDeletedAt($dateTime): void
    {
        $this->deletedAt = $dateTime;
    }
}
