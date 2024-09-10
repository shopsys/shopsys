<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\TranslatableInterface;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile;
use Shopsys\FrameworkBundle\Model\Localization\TranslatableEntityTrait;

/**
 * @ORM\Table(name="uploaded_files")})
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation translation(?string $locale = null)
 */
class UploadedFile extends AbstractUploadedFile implements TranslatableInterface
{
    use TranslatableEntityTrait;

    protected const string UPLOAD_KEY = 'uploadedFile';

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Prezent\Doctrine\Translatable\TranslationInterface|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation>
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

        /** @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation $translation */
        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @return string
     */
    protected function getUploadKey(): string
    {
        return static::UPLOAD_KEY;
    }

    /**
     * @return string
     */
    protected function getFileForUploadCategory(): string
    {
        return '';
    }
}
