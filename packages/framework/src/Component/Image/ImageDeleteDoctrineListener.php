<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageDeleteDoctrineListener
{
    public function __construct(
        protected readonly ImageConfig $imageConfig,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($this->imageConfig->hasImageConfig($entity)) {
            $this->deleteEntityImages($entity, $args->getObjectManager());
        } elseif ($entity instanceof Image) {
            $this->imageFacade->deleteImageFiles($entity);
        }
    }

    protected function deleteEntityImages(object $entity, EntityManagerInterface $em): void
    {
        $images = $this->imageFacade->getAllImagesByEntity($entity);

        foreach ($images as $image) {
            $em->remove($image);
        }
    }
}
