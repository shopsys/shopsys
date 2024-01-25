<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="images", indexes={@ORM\Index(columns={"entity_name", "entity_id", "type"})})
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Component\Image\ImageTranslation translation(?string $locale = null)
 */
class Image extends AbstractTranslatableEntity implements EntityFileUploadInterface
{
    protected const UPLOAD_KEY = 'image';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Component\Image\ImageTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Component\Image\ImageTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    protected $entityName;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $entityId;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    protected $extension;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * @var \DateTime
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
     * @param string[] $namesIndexedByLocale
     * @param string|null $temporaryFilename
     * @param string|null $type
     */
    public function __construct(
        string $entityName,
        int $entityId,
        array $namesIndexedByLocale,
        ?string $temporaryFilename,
        ?string $type,
    ) {
        $this->entityName = $entityName;
        $this->entityId = $entityId;
        $this->translations = new ArrayCollection();
        $this->setNames($namesIndexedByLocale);
        $this->type = $type;
        $this->setTemporaryFilename($temporaryFilename);
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
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

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\ImageTranslation
     */
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
    public function getTemporaryFilesForUpload(): array
    {
        $files = [];

        if ($this->temporaryFilename !== null) {
            $files[static::UPLOAD_KEY] = new FileForUpload(
                $this->temporaryFilename,
                true,
                $this->entityName,
                $this->type . '/',
                FileNamingConvention::TYPE_ID,
            );
        }

        return $files;
    }

    /**
     * @param string $key
     * @param string $originalFilename
     */
    public function setFileAsUploaded(string $key, string $originalFilename): void
    {
        if ($key !== static::UPLOAD_KEY) {
            throw new InvalidFileKeyException($key);
        }

        $this->extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
    }

    /**
     * @param string $key
     */
    public function setFileKeyAsUploaded(string $key): void
    {
        if ($key !== static::UPLOAD_KEY) {
            throw new InvalidFileKeyException($key);
        }

        $this->temporaryFilename = null;
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
     * @param int $position
     */
    public function setPosition(int $position): void
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
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     */
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

    /**
     * @param string|null $friendlyUrlSlug
     * @return string
     */
    public function getSeoFilename(?string $friendlyUrlSlug): string
    {
        $slug = '';

        if ($friendlyUrlSlug !== null) {
            $slug = $friendlyUrlSlug . '_';
        }

        return  $slug . $this->id . '.' . $this->extension;
    }
}
