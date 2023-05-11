<?php

namespace Shopsys\FrameworkBundle\Model\Security\Filesystem;

use FM\ElfinderBundle\Configuration\ElFinderConfigurationReader;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException;

class FilemanagerAccess
{
    protected static ?FilemanagerAccess $self = null;

    protected string $filemanagerUploadDir;

    protected ElFinderConfigurationReader $elFinderConfigurationReader;

    protected FilepathComparator $filepathComparator;

    /**
     * @param mixed $filamanagerUploadDir
     * @param \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader $elFinderConfigurationReader
     * @param \Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator $filepathComparator
     */
    public function __construct(
        $filamanagerUploadDir,
        ElFinderConfigurationReader $elFinderConfigurationReader,
        FilepathComparator $filepathComparator
    ) {
        $this->filemanagerUploadDir = realpath($filamanagerUploadDir);
        $this->elFinderConfigurationReader = $elFinderConfigurationReader;
        $this->filepathComparator = $filepathComparator;
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
