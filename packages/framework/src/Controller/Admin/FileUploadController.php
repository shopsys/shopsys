<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\FileUpload\Exception\FileUploadException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FileUploadController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Twig\FileThumbnail\FileThumbnailExtension $fileThumbnailExtension
     */
    public function __construct(
        protected readonly FileUpload $fileUpload,
        protected readonly FileThumbnailExtension $fileThumbnailExtension,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route(path: '/file-upload/')]
    public function uploadAction(Request $request)
    {
        $actionResult = [
            'status' => 'error',
            'code' => 0,
            'filename' => '',
            'message' => t('Unexpected error occurred, file was not uploaded.'),
        ];
        $file = $request->files->get('file');

        if ($file instanceof UploadedFile) {
            try {
                $temporaryFilename = $this->fileUpload->upload($file);
                $fileThumbnailInfo = $this->fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename(
                    $temporaryFilename,
                );

                $actionResult = [
                    'status' => 'success',
                    'filename' => $temporaryFilename,
                    'iconType' => $fileThumbnailInfo->getIconType(),
                    'imageThumbnailUri' => $fileThumbnailInfo->getImageUri(),
                ];
                $actionResult['status'] = 'success';
                $actionResult['filename'] = $temporaryFilename;
            } catch (FileUploadException $ex) {
                $actionResult['status'] = 'error';
                $actionResult['code'] = $ex->getCode();
                $actionResult['message'] = $ex->getMessage();
            }
        }

        return new JsonResponse($actionResult);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route(path: '/file-upload/delete-temporary-file/')]
    public function deleteTemporaryFileAction(Request $request)
    {
        $filename = $request->get('filename');

        if ($filename === null) {
            return new JsonResponse(false);
        }

        $actionResult = $this->fileUpload->tryDeleteTemporaryFile($filename);

        return new JsonResponse($actionResult);
    }
}
