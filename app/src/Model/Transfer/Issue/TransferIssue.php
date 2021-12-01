<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

use App\Model\Transfer\Transfer;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(
 *     name="transfer_issues",
 *     indexes={
 *          @ORM\Index(columns={"created_at"}),
 *      }
 * )
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class TransferIssue
{
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Model\Transfer\Transfer
     * @ORM\ManyToOne(targetEntity="App\Model\Transfer\Transfer")
     * @ORM\JoinColumn(name="transfer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $transfer;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    private $severity;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $message;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @param \App\Model\Transfer\Transfer $transfer
     * @param \App\Model\Transfer\Issue\TransferIssueData $transferIssueData
     */
    public function __construct(Transfer $transfer, TransferIssueData $transferIssueData)
    {
        $this->createdAt = new DateTime();
        $this->transfer = $transfer;
        $this->severity = $transferIssueData->severity;
        $this->message = $transferIssueData->message;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Transfer\Transfer
     */
    public function getTransfer(): Transfer
    {
        return $this->transfer;
    }

    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $dateTime
     * @return $this
     */
    public function setDeletedAt(DateTime $dateTime): self
    {
        $this->deletedAt = $dateTime;

        return $this;
    }
}
