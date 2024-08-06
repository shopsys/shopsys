<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

interface CustomerUploadedFileFactoryInterface
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param int $position
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    public function create(
        string $entityName,
        int $entityId,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        int $position = 0,
        ?CustomerUser $customerUser = null,
    ): CustomerUploadedFile;

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param int $existingFilesCount
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
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
    ): array;
}
