<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Image;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;

class ProductReviewImageDataFactory
{
    public function __construct(
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
    ) {
    }

    public function create(): ProductReviewImageData
    {
        return $this->createInstance();
    }

    public function createFromProductReviewImage(ProductReviewImage $productReviewImage): ProductReviewImageData
    {
        $productReviewImageData = $this->createInstance();

        $productReviewImageData->id = $productReviewImage->getId();
        $productReviewImageData->rejectionReason = $productReviewImage->getRejectionReason();
        $productReviewImageData->file = $this->customerUploadedFileDataFactory->createByEntity($productReviewImage);

        return $productReviewImageData;
    }

    protected function createInstance(): ProductReviewImageData
    {
        return new ProductReviewImageData();
    }
}
