<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Exception;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\UploadedFileResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerUploadedFileController
{
    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly UploadedFileResponseFactory $uploadedFileResponseFactory,
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

            return $this->uploadedFileResponseFactory->createDownloadResponse($this->customerUploadedFileFacade, $uploadedFile);
        } catch (Exception $ex) {
            throw new NotFoundRedirectToStorefrontException(sprintf('File "%s" not found.', $uploadedFilename), $ex);
        }
    }

    public function viewAction(Request $request, int $uploadedFileId, string $uploadedFilename): Response
    {
        try {
            $hash = $request->query->getString('hash');
            $uploadedFile = $this->getCustomerUploadedFile($uploadedFilename, $uploadedFileId, $hash);

            if (!$this->customerUploadedFileFacade->isUploadedFileViewableInBrowser($uploadedFile)) {
                return $this->downloadAction($request, $uploadedFileId, $uploadedFilename);
            }

            return $this->uploadedFileResponseFactory->createInlineViewResponse($this->customerUploadedFileFacade, $uploadedFile);
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
