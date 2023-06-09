<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Twig;

use Shopsys\FrameworkBundle\Twig\ImageExtension as BaseImageExtension;
use Shopsys\ReadModelBundle\Image\ImageView;

class ImageExtension extends BaseImageExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\ReadModelBundle\Image\ImageView|object|null $imageView
     * @param array $attributes
     * @return string
     */
    public function getImageHtml($imageView, array $attributes = []): string
    {
        if ($imageView === null) {
            return $this->getNoimageHtml($attributes);
        }

        if ($imageView instanceof ImageView) {
            $this->preventDefault($attributes);

            $entityName = $imageView->getEntityName();

            $attributes['src'] = $this->imageFacade->getImageUrlFromAttributes(
                $this->domain->getCurrentDomainConfig(),
                $imageView->getId(),
                $imageView->getExtension(),
                $entityName,
                $imageView->getType(),
                $attributes['size'],
            );

            $additionalImagesData = $this->imageFacade->getAdditionalImagesDataFromAttributes(
                $this->domain->getCurrentDomainConfig(),
                $imageView->getId(),
                $imageView->getExtension(),
                $entityName,
                $imageView->getType(),
                $attributes['size'],
            );

            $attributes['alt'] = $imageView->getName();

            return $this->getImageHtmlByEntityName($attributes, $entityName, $additionalImagesData);
        }

        return parent::getImageHtml($imageView, $attributes);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\ReadModelBundle\Image\ImageView|object $imageView
     * @param string|null $sizeName
     * @param string|null $type
     * @return string
     */
    public function getImageUrl($imageView, $sizeName = null, $type = null)
    {
        if ($imageView instanceof ImageView) {
            $entityName = $imageView->getEntityName();

            return $this->imageFacade->getImageUrlFromAttributes(
                $this->domain->getCurrentDomainConfig(),
                $imageView->getId(),
                $imageView->getExtension(),
                $entityName,
                $type,
                $sizeName,
            );
        }

        return parent::getImageUrl($imageView, $sizeName, $type);
    }
}
