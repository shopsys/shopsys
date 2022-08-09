<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;

class ImageDeleteDoctrineListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    protected $imageConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        ImageConfig $imageConfig,
        ImageFacade $imageFacade
    ) {
        $this->imageConfig = $imageConfig;
        $this->imageFacade = $imageFacade;
    }

    /**
     * Prevent ServiceCircularReferenceException (DoctrineListener cannot be dependent on the EntityManager)
     *
     * @return \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected function getImageFacade(): ImageFacade
    {
        return $this->imageFacade;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
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
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    protected function deleteEntityImages(object $entity, EntityManagerInterface $em): void
    {
        $images = $this->getImageFacade()->getAllImagesByEntity($entity);
        foreach ($images as $image) {
            $em->remove($image);
        }
    }
}
