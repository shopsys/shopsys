<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;

/**
 * @ORM\Table(name="uploaded_files", indexes={@ORM\Index(columns={"entity_name", "entity_id"})})
 * @ORM\Entity
 */
class UploadedFile implements EntityFileUploadInterface
{
    const UPLOAD_KEY = 'uploadedFile';

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
     * @ORM\Column(type="string", length=5)
     */
    protected $extension;

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

    public function __construct(string $entityName, int $entityId, ?string $temporaryFilename)
    {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
        $this->setTemporaryFilename($temporaryFilename);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    public function getTemporaryFilesForUpload(): array
    {
        if ($this->temporaryFilename === null) {
            return [];
        }

        return [
            self::UPLOAD_KEY => new FileForUpload(
                $this->temporaryFilename,
                false,
                $this->entityName,
                null,
                FileNamingConvention::TYPE_ID
            ),
        ];
    }
    
    public function setFileAsUploaded(string $key, string $originalFilename): void
    {
        if ($key === self::UPLOAD_KEY) {
            $this->extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        } else {
            throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException($key);
        }
    }

    public function setTemporaryFilename(?string $temporaryFilename): void
    {
        $this->temporaryFilename = $temporaryFilename;
        // workaround: Entity must be changed so that preUpdate and postUpdate are called
        $this->modifiedAt = new DateTime();
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

    public function getExtension(): string
    {
        return $this->extension;
    }
}
