<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security\Filesystem;

use FM\ElfinderBundle\Configuration\ElFinderConfigurationReader;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException;

class FilemanagerAccess
{
    protected static ?FilemanagerAccess $self = null;

    protected string $filemanagerUploadDir;

    public function __construct(
        mixed $filemanagerUploadDir,
        protected readonly ElFinderConfigurationReader $elFinderConfigurationReader,
        protected readonly FilepathComparator $filepathComparator,
    ) {
        $filemanagerUploadDir = realpath($filemanagerUploadDir);

        if ($filemanagerUploadDir !== false) {
            $this->filemanagerUploadDir = $filemanagerUploadDir;
        }
    }

    /**
     * @see \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader::access()
     */
    public function isPathAccessible(string $attr, string $path, ?string $data, ?string $volume): ?bool
    {
        if (!$this->filepathComparator->isPathWithinDirectory($path, $this->filemanagerUploadDir)) {
            return false;
        }

        return $this->elFinderConfigurationReader->access($attr, $path, $data, $volume);
    }

    public static function injectSelf(self $filemanagerAccess): void
    {
        self::$self = $filemanagerAccess;
    }

    public static function detachSelf(): void
    {
        self::$self = null;
    }

    /**
     * @see \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader::access()
     */
    public static function isPathAccessibleStatic(string $attr, string $path, ?string $data, ?string $volume): ?bool
    {
        if (self::$self === null) {
            throw new InstanceNotInjectedException();
        }

        return self::$self->isPathAccessible($attr, $path, $data, $volume);
    }
}
