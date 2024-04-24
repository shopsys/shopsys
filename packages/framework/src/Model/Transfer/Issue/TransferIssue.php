<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Shopsys\FrameworkBundle\Model\Transfer\Transfer;

/**
 * @ORM\Table(
 *     name="transfer_issues",
 *     indexes={
 *          @ORM\Index(columns={"created_at", "deleted_at", "transfer_id"}),
 *      }
 * )
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class TransferIssue
{
    public const string SEVERITY_ERROR = 'error';
    public const string SEVERITY_WARNING = 'warning';
    public const string SEVERITY_CRITICAL = 'critical';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transfer\Transfer
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transfer\Transfer")
     * @ORM\JoinColumn(name="transfer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $transfer;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    protected $severity;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $message;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Transfer $transfer
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueData $transferIssueData
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setDeletedAt($dateTime): void
    {
        $this->deletedAt = $dateTime;
    }
}
