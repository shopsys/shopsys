<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageDeleteDoctrineListener
{
    public function __construct(
        protected readonly ImageConfig $imageConfig,
        protected readonly ImageFacade $imageFacade,
    ) {
    }

    /**
     * Prevent ServiceCircularReferenceException (DoctrineListener cannot be dependent on the EntityManager)
     *
     * @return \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected function getImageFacade()
    {
        return $this->imageFacade;
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($this->imageConfig->hasImageConfig($entity)) {
            $this->deleteEntityImages($entity, $args->getEntityManager());
        } elseif ($entity instanceof Image) {
            $this->getImageFacade()->deleteImageFiles($entity);
        }
    }

    /**
     * @param object $entity
     */
    protected function deleteEntityImages($entity, EntityManagerInterface $em): void
    {
        $images = $this->getImageFacade()->getAllImagesByEntity($entity);

        foreach ($images as $image) {
            $em->remove($image);
        }
    }
}
