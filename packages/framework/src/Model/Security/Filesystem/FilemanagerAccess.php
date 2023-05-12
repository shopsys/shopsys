<?php

namespace Shopsys\FrameworkBundle\Model\Security\Filesystem;

use FM\ElfinderBundle\Configuration\ElFinderConfigurationReader;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException;

class FilemanagerAccess
{
    protected static ?FilemanagerAccess $self = null;

    protected string $filemanagerUploadDir;

    /**
     * @param mixed $filamanagerUploadDir
     * @param \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader $elFinderConfigurationReader
     * @param \Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator $filepathComparator
     */
    public function __construct(
        $filamanagerUploadDir,
        protected readonly ElFinderConfigurationReader $elFinderConfigurationReader,
        protected readonly FilepathComparator $filepathComparator
    ) {
        $this->filemanagerUploadDir = realpath($filamanagerUploadDir);
    }

    /**
     * @see \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader::access()
     * @param string $attr
     * @param string $path
     * @param string|null $data
     * @param string|null $volume
     * @return bool|null
     */
    public function isPathAccessible($attr, $path, $data, $volume)
    {
        if (!$this->filepathComparator->isPathWithinDirectory($path, $this->filemanagerUploadDir)) {
            return false;
        }

        return $this->elFinderConfigurationReader->access($attr, $path, $data, $volume);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess $filemanagerAccess
     */
    public static function injectSelf(self $filemanagerAccess)
    {
        self::$self = $filemanagerAccess;
    }

    public static function detachSelf()
    {
        self::$self = null;
    }

    /**
     * @see \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader::access()
     * @param string $attr
     * @param string $path
     * @param string|null $data
     * @param string|null $volume
     * @return bool|null
     */
    public static function isPathAccessibleStatic($attr, $path, $data, $volume)
    {
        if (self::$self === null) {
            throw new InstanceNotInjectedException();
        }

        return self::$self->isPathAccessible($attr, $path, $data, $volume);
    }
}
