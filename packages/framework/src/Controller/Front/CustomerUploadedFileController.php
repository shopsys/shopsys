<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerUploadedFileController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly FilesystemOperator $filesystem,
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
    public function viewAction(
        Request $request,
        int $uploadedFileId,
        string $uploadedFilename,
    ): StreamedResponse {
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
     * @param string $hash
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    protected function getCustomerUploadedFile(
        string $uploadedFilename,
        int $uploadedFileId,
        string $hash,
    ): CustomerUploadedFile {
        $uploadedFileSlug = pathinfo($uploadedFilename, PATHINFO_FILENAME);
        $uploadedFileExtension = pathinfo($uploadedFilename, PATHINFO_EXTENSION);

        return $this->customerUploadedFileFacade->getByIdSlugExtensionAndCustomerUserOrHash(
            $uploadedFileId,
            $uploadedFileSlug,
            $uploadedFileExtension,
            null,
            $hash,
        );
    }
}
