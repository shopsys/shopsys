<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerUploadedFileController
{
    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    public function downloadAction(
        Request $request,
        int $uploadedFileId,
        string $uploadedFilename,
    ): DownloadFileResponse {
        try {
            $hash = $request->query->getString('hash');
            $uploadedFile = $this->getCustomerUploadedFile($uploadedFilename, $uploadedFileId, $hash);
            $filePath = $this->customerUploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            return new DownloadFileResponse(
                $uploadedFile->getNameWithExtension(),
                $this->filesystem->read($filePath),
                $this->filesystem->mimeType($filePath),
            );
        } catch (Exception $ex) {
            throw new NotFoundRedirectToStorefrontException(sprintf('File "%s" not found.', $uploadedFilename), $ex);
        }
    }

    public function viewAction(Request $request, int $uploadedFileId, string $uploadedFilename): StreamedResponse
    {
        try {
            $hash = $request->query->getString('hash');
            $uploadedFile = $this->getCustomerUploadedFile($uploadedFilename, $uploadedFileId, $hash);
            $filePath = $this->customerUploadedFileFacade->getAbsoluteUploadedFileFilepath($uploadedFile);

            return new StreamedResponse(function () use ($filePath): void {
                $stream = $this->filesystem->readStream($filePath);
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => $this->filesystem->mimeType($filePath),
                'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $uploadedFile->getNameWithExtension()),
            ]);
        } catch (Exception $ex) { // never disclose backend error pages
            throw new NotFoundRedirectToStorefrontException(sprintf('File "%s" not found.', $uploadedFilename), $ex);
        }
    }

    protected function getCustomerUploadedFile(
        string $uploadedFilename,
        int $uploadedFileId,
        string $hash,
    ): CustomerUploadedFile {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        if ($this->customerUploadedFileFacade->isAccessToFileDenied($hash)) {
            throw new AccessDeniedException(sprintf('%s.%s', $uploadedFileSlug, $uploadedFileExtension));
        }

        return $this->customerUploadedFileFacade->getByIdSlugExtensionAndHash(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
            $hash,
        );
    }
}
