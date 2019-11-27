<?php

declare(strict_types=1);

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
    protected const UPLOAD_KEY = 'uploadedFile';

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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $modifiedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $temporaryFilename;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param string|null $temporaryFilename
     */
    public function __construct(string $entityName, int $entityId, string $type, ?string $temporaryFilename)
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
        if ($this->temporaryFilename === null) {
            return [];
        }

        return [
            static::UPLOAD_KEY => new FileForUpload(
                $this->temporaryFilename,
                false,
                $this->entityName,
                null,
                FileNamingConvention::TYPE_ID
            ),
        ];
    }

    /**
     * @param string $key
     * @param string $originalFilename
     */
    public function setFileAsUploaded(string $key, string $originalFilename): void
    {
        if ($key === static::UPLOAD_KEY) {
            $this->extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        } else {
            throw new \Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException($key);
        }
    }

    /**
     * @param string|null $temporaryFilename
     */
    public function setTemporaryFilename(?string $temporaryFilename): void
    {
        $this->temporaryFilename = $temporaryFilename;
        // workaround: Entity must be changed so that preUpdate and postUpdate are called
        $this->modifiedAt = new DateTime();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->id . '.' . $this->extension;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     */
    public function checkForDelete(string $entityName, int $entityId): void
    {
        if ($this->entityName !== $entityName || $this->entityId !== $entityId) {
            throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException(
                sprintf(
                    'Entity "%s" with ID "%s" does not own file with ID "%s"',
                    $entityName,
                    $entityId,
                    $this->id
                )
            );
        }
    }
}
