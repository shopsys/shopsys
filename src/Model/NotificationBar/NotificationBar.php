<?php

declare(strict_types=1);

namespace App\Model\NotificationBar;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="notification_bars")
 */
class NotificationBar
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validityFrom;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validityTo;

    /**
     * @var string
     * @ORM\Column(type="string", length=7)
     */
    protected $rgbColor;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @param \App\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    public function __construct(NotificationBarData $notificationBarData)
    {
        $this->domainId = $notificationBarData->domainId;
        $this->text = $notificationBarData->text;
        $this->validityFrom = $notificationBarData->validityFrom;
        $this->validityTo = $notificationBarData->validityTo;
        $this->rgbColor = $notificationBarData->rgbColor;
        $this->hidden = $notificationBarData->hidden;
    }

    /**
     * @param \App\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    public function edit(NotificationBarData $notificationBarData): void
    {
        $this->domainId = $notificationBarData->domainId;
        $this->text = $notificationBarData->text;
        $this->validityFrom = $notificationBarData->validityFrom;
        $this->validityTo = $notificationBarData->validityTo;
        $this->rgbColor = $notificationBarData->rgbColor;
        $this->hidden = $notificationBarData->hidden;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getRgbColor(): string
    {
        return $this->rgbColor;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidityFrom(): ?DateTime
    {
        return $this->validityFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidityTo(): ?DateTime
    {
        return $this->validityTo;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }
}
