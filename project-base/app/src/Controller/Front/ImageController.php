<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageException;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageGeneratorFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends AbstractController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageGeneratorFacade $imageGeneratorFacade
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        private readonly ImageGeneratorFacade $imageGeneratorFacade,
        private readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @param mixed $sizeName
     * @param mixed $imageId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getImageAction(string $entityName, ?string $type, $sizeName, $imageId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if ($sizeName === ImageConfig::DEFAULT_SIZE_NAME) {
            $sizeName = null;
        }

        try {
            $imageFilepath = $this->imageGeneratorFacade->generateImageAndGetFilepath(
                $entityName,
                $imageId,
                $type,
                $sizeName,
            );
        } catch (ImageException $e) {
            $message = sprintf(
                'Generate image for entity "%s" (type=%s, size=%s, imageId=%s) failed',
                $entityName,
                $type,
                $sizeName,
                $imageId,
            );

            throw $this->createNotFoundException($message, $e);
        }

        return $this->sendImage($imageFilepath);
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @param mixed $sizeName
     * @param int $imageId
     * @param int $additionalIndex
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getAdditionalImageAction(string $entityName, ?string $type, $sizeName, int $imageId, int $additionalIndex): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if ($sizeName === ImageConfig::DEFAULT_SIZE_NAME) {
            $sizeName = null;
        }

        try {
            $imageFilepath = $this->imageGeneratorFacade->generateAdditionalImageAndGetFilepath(
                $entityName,
                $imageId,
                $additionalIndex,
                $type,
                $sizeName,
            );
        } catch (ImageException $e) {
            $message = sprintf(
                'Generate image for entity "%s" (type=%s, size=%s, imageId=%s, additionalIndex=%s) failed',
                $entityName,
                $type,
                $sizeName,
                $imageId,
                $additionalIndex,
            );

            throw $this->createNotFoundException($message, $e);
        }

        return $this->sendImage($imageFilepath);
    }

    /**
     * @param string $imageFilepath
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function sendImage(string $imageFilepath): StreamedResponse
    {
        try {
            $fileStream = $this->filesystem->readStream($imageFilepath);
            $headers = [
                'content-type' => $this->filesystem->mimeType($imageFilepath),
                'content-size' => $this->filesystem->fileSize($imageFilepath),
                'Access-Control-Allow-Origin' => '*',
            ];

            $callback = function () use ($fileStream): void {
                $out = fopen('php://output', 'wb');
                stream_copy_to_stream($fileStream, $out);
            };

            return new StreamedResponse($callback, 200, $headers);
        } catch (Exception $e) {
            $message = 'Response with file "' . $imageFilepath . '" failed.';

            throw $this->createNotFoundException($message, $e);
        }
    }
}
