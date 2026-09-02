<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\Exception\InvalidFileKeyException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Symfony\Component\Clock\DatePoint;

#[ORM\MappedSuperclass]
abstract class AbstractUploadedFile implements EntityFileUploadInterface, UploadedFileInterface
{
    /**
     * @var string|null
     */
    protected $temporaryFilename;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 5)]
    protected $extension;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable')]
    protected $modifiedAt;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $filesize;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $slug {
        set {
            $this->slug = TransformStringHelper::createFriendlyUrlSlug($value);
        }
    }

    public function getSlugWithExtension(): string
    {
        return $this->slug . '.' . $this->extension;
    }

    public function getTemporaryFilename(): string
    {
        return $this->temporaryFilename;
    }

    #[Override]
    public function getFilename(): string
    {
        return $this->id . '.' . $this->extension;
    }

    #[Override]
    public function setFileAsUploaded(string $key, string $originalFilename): void
    {
        if ($key !== $this::getUploadKey()) {
            throw new InvalidFileKeyException($key);
        }

        $this->extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
    }

    #[Override]
    public function setFileKeyAsUploaded(string $key): void
    {
        if ($key !== $this::getUploadKey()) {
            throw new InvalidFileKeyException($key);
        }

        $this->temporaryFilename = null;
    }

    /**
     * @param string $name
     */
    #[Override]
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getNameWithExtension(): string
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
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
     * @param string $slug
     */
    #[Override]
    public function setSlug($slug): void
    {
        $this->slug = $slug;
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
    public function getName()
    {
        return $this->name;
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

    abstract protected function getUploadKey(): string;

    abstract protected function getFileForUploadCategory(): string;

    /**
     * @return \Shopsys\FrameworkBundle\Component\FileUpload\FileForUpload[]
     */
    #[Override]
    public function getTemporaryFilesForUpload(): array
    {
        $files = [];

        if ($this->temporaryFilename !== null) {
            $files[$this->getUploadKey()] = new FileForUpload(
                $this->temporaryFilename,
                static::class,
                $this->getFileForUploadCategory(),
                FileNamingConvention::TYPE_ID,
            );
        }

        return $files;
    }

    public function updateFile(string $temporaryFilename, int $filesize): void
    {
        $this->temporaryFilename = $temporaryFilename;
        $this->filesize = $filesize;
        // workaround: Entity must be changed so that preUpdate and postUpdate are called
        $this->modifiedAt = new DatePoint();
    }
}
