<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Exception;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Component\HttpFoundation\UploadedFileResponseFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\HttpFoundation\Response;

class UploadedFileController
{
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileResponseFactory $uploadedFileResponseFactory,
    ) {
    }

    public function downloadAction(int $uploadedFileId, string $uploadedFilename): DownloadFileResponse
    {
        try {
            $uploadedFile = $this->getByIdSlugAndExtension($uploadedFileId, $uploadedFilename);

            return $this->uploadedFileResponseFactory->createDownloadResponse($this->uploadedFileFacade, $uploadedFile);
        } catch (Exception $e) {
            throw new NotFoundRedirectToStorefrontException(sprintf('Uploaded file with ID "%s" not found.', $uploadedFileId), $e);
        }
    }

    public function viewAction(int $uploadedFileId, string $uploadedFilename): Response
    {
        try {
            $uploadedFile = $this->getByIdSlugAndExtension($uploadedFileId, $uploadedFilename);

            if (!$this->uploadedFileFacade->isUploadedFileViewableInBrowser($uploadedFile)) {
                return $this->downloadAction($uploadedFileId, $uploadedFilename);
            }

            return $this->uploadedFileResponseFactory->createInlineViewResponse($this->uploadedFileFacade, $uploadedFile);
        } catch (Exception $e) {
            throw new NotFoundRedirectToStorefrontException(sprintf('Uploaded file with ID "%s" not found.', $uploadedFileId), $e);
        }
    }

    protected function getByIdSlugAndExtension(
        int $uploadedFileId,
        string $uploadedFilename,
    ): UploadedFile {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        return $this->uploadedFileFacade->getByIdSlugAndExtension(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
        );
    }
}
