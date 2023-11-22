<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload;

use Shopsys\FrameworkBundle\Component\FileUpload\Exception\UnresolvedNamingConventionException;

class FileNamingConvention
{
    public const TYPE_ID = 1;
    protected const TYPE_ORIGINAL_NAME = 2;

    /**
     * @param int $namingConventionType
     * @param string $originalFilename
     * @param int|null $entityId
     * @return string
     */
    public function getFilenameByNamingConvention($namingConventionType, $originalFilename, $entityId = null): string
    {
        if ($namingConventionType === self::TYPE_ID && is_int($entityId)) {
            return $entityId . '.' . pathinfo($originalFilename, PATHINFO_EXTENSION);
        }

        if ($namingConventionType === static::TYPE_ORIGINAL_NAME) {
            return $originalFilename;
        }
        $message = 'Naming convention ' . $namingConventionType . ' cannot by resolved to filename';

        throw new UnresolvedNamingConventionException($message);
    }
}
