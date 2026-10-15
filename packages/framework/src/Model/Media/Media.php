<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Media;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImageFolder;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

#[AsMcpTable(exposed: false)]
#[ORM\Table(name: 'media')]
#[ORM\Entity]
#[EntityImageFolder('media')]
#[EntityImage]
class Media implements Presentable
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $title;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $width;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $height;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $createdAt;

    public function __construct(MediaData $mediaData)
    {
        $this->createdAt = new DatePoint();
        $this->setData($mediaData);
    }

    public function edit(MediaData $mediaData): void
    {
        $this->setData($mediaData);
    }

    protected function setData(MediaData $mediaData): void
    {
        $this->title = $mediaData->title;
        $this->width = $mediaData->width;
        $this->height = $mediaData->height;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    #[Override]
    public function toHumanReadable(): string
    {
        return $this->title;
    }
}
