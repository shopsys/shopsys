<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Image;

use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * Removes the public copy of a deleted review photo, the private uploaded file
 * is deleted by CustomerUploadedFileDeleteDoctrineListener
 */
class ProductReviewImageDeleteDoctrineListener
{
    public function __construct(
        protected readonly ProductReviewImagePublisher $productReviewImagePublisher,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof ProductReviewImage) {
            $this->productReviewImagePublisher->unpublish($entity);
        }
    }
}
