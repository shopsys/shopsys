<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Image;

class ProductReviewImageData
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $rejectionReason;

    /**
     * @var \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData|null
     */
    public $file;
}
