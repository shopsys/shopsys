<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ImagesIdsToImagesTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageRepository
     */
    public function __construct(ImageFacade $imageRepository)
    {
        $this->imageFacade = $imageRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image[]|null $images
     * @return int[]
     */
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
    public function reverseTransform($imagesIds): array
    {
        $images = [];

        if (is_array($imagesIds)) {
            foreach ($imagesIds as $imageId) {
                try {
                    $images[] = $this->imageFacade->getById($imageId);
                } catch (ImageNotFoundException $e) {
                    throw new TransformationFailedException('Image not found', 0, $e);
                }
            }
        }

        return $images;
    }
}
