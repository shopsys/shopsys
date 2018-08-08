<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

/**
 * @ORM\Table(name="images", indexes={@ORM\Index(columns={"entity_name", "entity_id", "type"})})
 * @ORM\Entity
 */
class Image implements EntityFileUploadInterface
{
    const UPLOAD_KEY = 'image';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $entityName;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
     */
    protected $extension;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;

    /**
     * @var string|null
     */
    protected $temporaryFilename;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @param string|null $temporaryFilename
     */
    public function __construct($entityName, $entityId, $type, $temporaryFilename)
    {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
        $this->type = $type;
        $this->setTemporaryFilename($temporaryFilename);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    public function getTemporaryFilesForUpload(): array
    {
        $files = [];
        if ($this->temporaryFilename !== null) {
            $files[self::UPLOAD_KEY] = new FileForUpload(
                $this->temporaryFilename,
                true,
                $this->entityName,
                $this->type . '/' . ImageConfig::ORIGINAL_SIZE_NAME,
                FileNamingConvention::TYPE_ID
            );
        }
        return $files;
    }

    /**
     * @param string $key
     * @param string $originalFilename
     */
    public function setFileAsUploaded($key, $originalFilename)
    {
        if ($key === self::UPLOAD_KEY) {
            $this->extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        } else {
            throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException($key);
        }
    }

    /**
     * @param string|null $temporaryFilename
     */
    public function setTemporaryFilename($temporaryFilename)
    {
        $this->temporaryFilename = $temporaryFilename;
        // workaround: Entity must be changed so that preUpdate and postUpdate are called
        $this->modifiedAt = new DateTime();
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getFilename(): string
    {
        return $this->id . '.' . $this->extension;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getModifiedAt(): \DateTime
    {
        return $this->modifiedAt;
    }
}
