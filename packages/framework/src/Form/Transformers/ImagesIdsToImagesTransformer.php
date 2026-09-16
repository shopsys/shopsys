<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Symfony\Component\Form\DataTransformerInterface;

class ImagesIdsToImagesTransformer implements DataTransformerInterface
{
    protected ImageFacade $imageFacade;

    public function __construct(ImageFacade $imageRepository)
    {
        $this->imageFacade = $imageRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[]|null $images
     * @return int[]
     */
    #[Override]
    public function transform($images): array
    {
        $imagesIds = [];

        if (is_iterable($images)) {
            foreach ($images as $image) {
                $imagesIds[] = $image->getId();
            }
        }

        return $imagesIds;
    }

    /**
     * @param int[] $imagesIds
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    #[Override]
    public function reverseTransform($imagesIds): array
    {
        $images = [];

        if (is_array($imagesIds)) {
            foreach ($imagesIds as $imageId) {
                // the image is not in the submitted form, e.g. it was uploaded from another browser tab after the form was loaded
                if ($imageId === null || $imageId === '') {
                    continue;
                }

                try {
                    $images[] = $this->imageFacade->getById((int)$imageId);
                } catch (ImageNotFoundException) {
                    // the image was deleted from another browser tab after the form was loaded
                    continue;
                }
            }
        }

        return $images;
    }
}
