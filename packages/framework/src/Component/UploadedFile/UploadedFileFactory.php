<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly FileUpload $fileUpload,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

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
    ): UploadedFile {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);

        $entityClassName = $this->entityNameResolver->resolve(UploadedFile::class);

        return new $entityClassName(
            pathinfo($temporaryFilepath, PATHINFO_BASENAME),
            $uploadedFilename,
            $namesIndexedByLocale,
        );
    }

    /**
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param array $names
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function createMultiple(
        array $temporaryFilenames,
        array $uploadedFilenames,
        array $names = [],
    ): array {
        $files = [];

        foreach ($temporaryFilenames as $key => $temporaryFilename) {
            $files[] = $this->create(
                $temporaryFilename,
                $uploadedFilenames[$key],
                $names[$key] ?? [],
            );
        }

        return $files;
    }
}
