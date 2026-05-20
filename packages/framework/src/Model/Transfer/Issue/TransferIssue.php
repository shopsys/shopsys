<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable]
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
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'transfer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Transfer::class)]
    protected $transfer;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    protected $severity;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text', nullable: false)]
    protected $message;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
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
