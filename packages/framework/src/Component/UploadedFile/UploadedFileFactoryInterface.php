<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

interface UploadedFileFactoryInterface
{
    /**
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param array $namesIndexedByLocale
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function create(
        string $temporaryFilename,
        string $uploadedFilename,
        array $namesIndexedByLocale = [],
    ): UploadedFile;

    /**
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param array $names
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function createMultiple(
        array $temporaryFilenames,
        array $uploadedFilenames,
        array $names,
    ): array;
}
