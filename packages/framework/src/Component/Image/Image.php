<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;
use Symfony\Component\Clock\DatePoint;

/**
 * @method \Shopsys\FrameworkBundle\Component\Image\ImageTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Component\Image\ImageTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'images')]
#[ORM\Index(columns: ['entity_name', 'entity_id', 'type'])]
#[ORM\Entity]
class Image extends AbstractTranslatableEntity implements EntityFileUploadInterface
{
    protected const string UPLOAD_KEY = 'image';
    public const int DEFAULT_IMAGE_POSITION = 0;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Component\Image\ImageTranslation>
     */
    #[Prezent\Translations(targetEntity: ImageTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100)]
    protected $entityName;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $entityId;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $type;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 5)]
    protected $extension;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $position;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $filesize;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $modifiedAt;

    /**
     * @var string|null
     */
    protected $temporaryFilename;

    /**
     * @param string[] $namesIndexedByLocale
     */
    public function __construct(
        string $entityName,
        int $entityId,
        array $namesIndexedByLocale,
        ?string $temporaryFilename,
        ?string $type,
        int $filesize,
    ) {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
        $this->translations = new ArrayCollection();
        $this->setNames($namesIndexedByLocale);
        $this->type = $type;
        $this->updateFile($temporaryFilename, $filesize);
        $this->position = static::DEFAULT_IMAGE_POSITION;
    }

    public function getName(?string $locale = null): ?string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    #[Override]
    protected function createTranslation(): ImageTranslation
    {
        return new ImageTranslation();
    }

    /**
     * @param string[] $names
     */
    public function setNames(array $names): void
    {
        foreach ($names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    #[Override]
    public function getTemporaryFilesForUpload(): array
    {
        $files = [];

        if ($this->temporaryFilename !== null) {
            $files[static::UPLOAD_KEY] = new FileForUpload(
                $this->temporaryFilename,
                self::class,
                $this->entityName,
                FileNamingConvention::TYPE_ID,
                $this->type . '/',
            );
        }

        return $files;
    }

    #[Override]
    public function setFileAsUploaded(string $key, string $originalFilename): void
    {
        if ($key !== static::UPLOAD_KEY) {
            throw new InvalidFileKeyException($key);
        }

        $this->extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
    }

    #[Override]
    public function setFileKeyAsUploaded(string $key): void
    {
        if ($key !== static::UPLOAD_KEY) {
            throw new InvalidFileKeyException($key);
        }

        $this->temporaryFilename = null;
    }

    public function updateFile(?string $temporaryFilename, int $filesize): void
    {
        $this->temporaryFilename = $temporaryFilename;
        $this->filesize = $filesize;
        // workaround: Entity must be changed so that preUpdate and postUpdate are called
        $this->modifiedAt = new DatePoint();
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function getFilename(): string
    {
        return $this->id . '.' . $this->extension;
    }

    /**
     * @return int|null
     */
    #[Override]
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @return int|null
     */
    #[Override]
    public function getFilesize()
    {
        return $this->filesize;
    }

    public function checkForDelete(string $entityName, int $entityId): void
    {
        if ($this->entityName !== $entityName || $this->entityId !== $entityId) {
            throw new ImageNotFoundException(
                sprintf(
                    'Entity %s with ID %s does not own image with ID %s',
                    $entityName,
                    $entityId,
                    $this->id,
                ),
            );
        }
    }

    public function getSeoFilename(?string $friendlyUrlSlug): string
    {
        $slug = '';

        if ($friendlyUrlSlug !== null) {
            $slug = $friendlyUrlSlug . '_';
        }

        return $slug . $this->id . '.' . $this->extension;
    }
}
