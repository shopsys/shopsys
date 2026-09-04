<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Image;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;

/**
 * Publication of a review photo is a physical copy of the private customer uploaded file
 * into the public content images directory, where it is served as a static file
 * with the standard resizing pipeline (nginx image location, imgproxy/CDN, WebP).
 * Unpublishing deletes the copy, so a rejected photo stops being served immediately.
 */
class ProductReviewImagePublisher
{
    protected const string PUBLIC_IMAGES_SUBDIR = 'productReviewImage';

    public function __construct(
        protected readonly FilesystemOperator $filesystem,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly CdnFacade $cdnFacade,
        protected readonly string $imageDir,
        protected readonly string $imageUrlPrefix,
    ) {
    }

    /**
     * Aligns the public copies of the photos with the moderation state - a photo is public
     * if and only if its review is approved and the photo itself is not rejected
     */
    public function reconcile(ProductReview $productReview): void
    {
        $isReviewPublic = $productReview->getStatus() === ProductReviewStatusEnum::STATUS_APPROVED;

        foreach ($productReview->getImages() as $productReviewImage) {
            if ($isReviewPublic && !$productReviewImage->isRejected()) {
                $this->publish($productReviewImage);
            } else {
                $this->unpublish($productReviewImage);
            }
        }
    }

    public function getPublicUrl(
        DomainConfig $domainConfig,
        ProductReviewImage $productReviewImage,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        return $this->cdnFacade->resolveDomainUrlForAssets($domainConfig)
            . $this->imageUrlPrefix
            . static::PUBLIC_IMAGES_SUBDIR . '/'
            . $this->getPublicFilename($productReviewImage, $customerUploadedFile);
    }

    protected function publish(ProductReviewImage $productReviewImage): void
    {
        foreach ($this->customerUploadedFileFacade->getUploadedFilesByEntity($productReviewImage) as $customerUploadedFile) {
            $stream = $this->filesystem->readStream(
                $this->customerUploadedFileFacade->getAbsoluteUploadedFileFilepath($customerUploadedFile),
            );

            $this->filesystem->writeStream(
                $this->getPublicFilepath($productReviewImage, $customerUploadedFile),
                $stream,
                ['visibility' => Visibility::PUBLIC],
            );

            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function unpublish(ProductReviewImage $productReviewImage): void
    {
        foreach ($this->customerUploadedFileFacade->getUploadedFilesByEntity($productReviewImage) as $customerUploadedFile) {
            $publicFilepath = $this->getPublicFilepath($productReviewImage, $customerUploadedFile);

            if ($this->filesystem->fileExists($publicFilepath)) {
                $this->filesystem->delete($publicFilepath);
            }
        }
    }

    protected function getPublicFilepath(
        ProductReviewImage $productReviewImage,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        return $this->imageDir
            . static::PUBLIC_IMAGES_SUBDIR . '/'
            . $this->getPublicFilename($productReviewImage, $customerUploadedFile);
    }

    protected function getPublicFilename(
        ProductReviewImage $productReviewImage,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        return $productReviewImage->getId() . '.' . $customerUploadedFile->getExtension();
    }
}
