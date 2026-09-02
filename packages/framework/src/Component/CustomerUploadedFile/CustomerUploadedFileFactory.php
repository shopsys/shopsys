<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\String\ByteString;

class CustomerUploadedFileFactory
{
    public function __construct(
        protected readonly FileUpload $fileUpload,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(
        string $entityName,
        int $entityId,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        int $position = 0,
        ?CustomerUser $customerUser = null,
    ): CustomerUploadedFile {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);
        $filesize = $this->fileUpload->getTemporaryFilesize($temporaryFilename);

        $entityClassName = $this->entityNameResolver->resolve(CustomerUploadedFile::class);

        $hash = ByteString::fromRandom(32)->toString();

        $uploadedFilename = pathinfo($uploadedFilename, PATHINFO_FILENAME);

        return new $entityClassName(
            $entityName,
            $entityId,
            $type,
            pathinfo($temporaryFilepath, PATHINFO_BASENAME),
            $uploadedFilename,
            $position,
            $hash,
            $filesize,
            $customerUser,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    public function createMultiple(
        string $entityName,
        int $entityId,
        string $type,
        array $temporaryFilenames,
        array $uploadedFilenames,
        int $existingFilesCount,
        ?CustomerUser $customerUser = null,
    ): array {
        $files = [];

        foreach ($temporaryFilenames as $key => $temporaryFilename) {
            $files[] = $this->create(
                $entityName,
                $entityId,
                $type,
                $temporaryFilename,
                $uploadedFilenames[$key],
                $existingFilesCount++,
                $customerUser,
            );
        }

        return $files;
    }
}
