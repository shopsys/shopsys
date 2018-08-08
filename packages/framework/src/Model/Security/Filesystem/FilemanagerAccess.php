<?php

namespace Shopsys\FrameworkBundle\Model\Security\Filesystem;

use FM\ElfinderBundle\Configuration\ElFinderConfigurationReader;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;

class FilemanagerAccess
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess|null
     */
    private static $self;

    /**
     * @var string
     */
    private $filemanagerUploadDir;

    /**
     * @var \FM\ElfinderBundle\Configuration\ElFinderConfigurationReader
     */
    private $elFinderConfigurationReader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator
     */
    private $filepathComparator;

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
     * @return bool|null
     */
    public static function isPathAccessibleStatic($attr, $path, $data, $volume)
    {
        if (self::$self === null) {
            throw new \Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException();
        }

        return self::$self->isPathAccessible($attr, $path, $data, $volume);
    }
}
