<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\NotificationBar;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'notification_bars')]
#[ORM\Entity]
#[EntityImage]
class NotificationBar
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'text')]
    protected $text;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $validityFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $validityTo;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 7)]
    protected $rgbColor;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    public function __construct(NotificationBarData $notificationBarData)
    {
        $this->setData($notificationBarData);
    }

    public function edit(NotificationBarData $notificationBarData): void
    {
        $this->setData($notificationBarData);
    }

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
