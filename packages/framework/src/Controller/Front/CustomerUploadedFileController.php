<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerUploadedFileController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     */
    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly FilesystemOperator $filesystem,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $uploadedFileId
     * @param string $uploadedFilename
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse
     */
    public function downloadAction(
        Request $request,
        int $uploadedFileId,
        string $uploadedFilename,
    ): DownloadFileResponse {
        $hash = $request->get('hash');
        $uploadedFile = $this->getCustomerUploadedFile($uploadedFilename, $uploadedFileId, $hash);
        $filePath = $this->customerUploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new DownloadFileResponse(
            $uploadedFile->getNameWithExtension(),
            $this->filesystem->read($filePath),
            $this->filesystem->mimeType($filePath),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $uploadedFileId
     * @param string $uploadedFilename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function viewAction(Request $request, int $uploadedFileId, string $uploadedFilename): StreamedResponse
    {
        $hash = $request->get('hash');
        $uploadedFile = $this->getCustomerUploadedFile($uploadedFilename, $uploadedFileId, $hash);
        $filePath = $this->customerUploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

        return new StreamedResponse(function () use ($filePath) {
            $stream = $this->filesystem->readStream($filePath);
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $this->filesystem->mimeType($filePath),
            'Content-Disposition' => sprintf('inline; filename="%s"', $uploadedFile->getNameWithExtension()),
        ]);
    }

    /**
     * @param string $uploadedFilename
     * @param int $uploadedFileId
     * @param string|null $hash
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    protected function getCustomerUploadedFile(
        string $uploadedFilename,
        int $uploadedFileId,
        ?string $hash,
    ): CustomerUploadedFile {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$hash && !$customerUser && !$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new AccessDeniedException(sprintf('%s.%s', $uploadedFileSlug, $uploadedFileExtension));
        }

        return $this->customerUploadedFileFacade->getByIdSlugExtensionAndCustomerUserOrHash(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
            $customerUser,
            $hash,
        );
    }
}
