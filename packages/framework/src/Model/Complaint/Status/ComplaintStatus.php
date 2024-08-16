<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Complaint\Status\Exception\ComplaintStatusDeletionForbiddenException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="complaint_statuses")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation translation(?string $locale = null)
 */
class ComplaintStatus extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation")
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=25)
     */
    protected $status;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     * @param string $status
     */
    public function __construct(ComplaintStatusData $complaintStatusData, string $status)
    {
        $this->translations = new ArrayCollection();
        $this->status = $status;
        $this->setData($complaintStatusData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     */
    public function edit(ComplaintStatusData $complaintStatusData): void
    {
        $this->setData($complaintStatusData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     */
    protected function setData(ComplaintStatusData $complaintStatusData): void
    {
        $this->setTranslations($complaintStatusData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusData $complaintStatusData
     */
    protected function setTranslations(ComplaintStatusData $complaintStatusData): void
    {
        foreach ($complaintStatusData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTranslation
     */
    protected function createTranslation()
    {
        return new ComplaintStatusTranslation();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function checkForDelete(): void
    {
        if ($this->status !== ComplaintStatusEnum::STATUS_IN_PROGRESS) {
            throw new ComplaintStatusDeletionForbiddenException($this);
        }
    }
}
