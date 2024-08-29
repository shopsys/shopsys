<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="uploaded_files")})
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation translation(?string $locale = null)
 */
class UploadedFile extends AbstractTranslatableEntity implements EntityFileUploadInterface
{
    protected const UPLOAD_KEY = 'uploadedFile';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    protected $extension;

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
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation")
     */
    protected $translations;

    /**
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param array<string, string> $namesIndexedByLocale
     */
    public function __construct(
        string $temporaryFilename,
        string $uploadedFilename,
        array $namesIndexedByLocale,
    ) {
        $this->setTemporaryFilename($temporaryFilename);
        $this->setNameAndSlug($uploadedFilename);
        $this->translations = new ArrayCollection();
        $this->setTranslatedNames($namesIndexedByLocale);
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
                false,
                '',
                null,
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

        $this->temporaryFilename = '';
    }

    /**
     * @param string $temporaryFilename
     */
    public function setTemporaryFilename(string $temporaryFilename): void
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
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameWithExtension(): string
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTemporaryFilename(): string
    {
        return $this->temporaryFilename;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getSlugWithExtension(): string
    {
        return $this->slug . '.' . $this->extension;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $temporaryFilename
     */
    public function setNameAndSlug(string $temporaryFilename): void
    {
        $filenameWithoutExtension = pathinfo($temporaryFilename, PATHINFO_FILENAME);

        $this->setName($filenameWithoutExtension);
        $this->setSlug(TransformString::stringToFriendlyUrlSlug($filenameWithoutExtension));
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation
     */
    protected function createTranslation()
    {
        return new UploadedFileTranslation();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param string[] $names
     */
    public function setTranslatedNames(array $names): void
    {
        foreach ($names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return string[]
     */
    public function getTranslatedNames(): array
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }
}
