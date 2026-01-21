<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Table(name: 'notification_bars')]
#[ORM\Entity]
class NotificationBar
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    protected $text;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $validityFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $validityTo;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 7)]
    protected $rgbColor;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    public function __construct(NotificationBarData $notificationBarData)
    {
        $this->setData($notificationBarData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    public function edit(NotificationBarData $notificationBarData): void
    {
        $this->setData($notificationBarData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarData $notificationBarData
     */
    protected function setData(NotificationBarData $notificationBarData): void
    {
        $this->uuid = $notificationBarData->uuid ?? Uuid::uuid4()->toString();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getRgbColor()
    {
        return $this->rgbColor;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getValidityFrom()
    {
        return $this->validityFrom;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getValidityTo()
    {
        return $this->validityTo;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }
}
