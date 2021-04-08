<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\ReadModelBundle\Twig\ImageExtension as BaseImageExtension;

class ImageExtension extends BaseImageExtension
{
    /**
     * @deprecated Method will be moved to framework in the next major
     * @param array $attributes
     * @param string $entityName
     * @param \Shopsys\FrameworkBundle\Component\Image\AdditionalImageData[] $additionalImagesData
     * @return string
     */
    protected function getImageHtmlByEntityName(array $attributes, $entityName, $additionalImagesData = []): string
    {
        $htmlAttributes = $this->extractHtmlAttributesFromAttributes($attributes);

        if ($this->isLazyLoadEnabled($attributes) === true) {
            $htmlAttributes = $this->makeHtmlAttributesLazyLoaded($htmlAttributes);
        }

        return $this->twigEnvironment->render('@ShopsysFramework/Common/image.html.twig', [
            'attr' => $htmlAttributes,
            'additionalImagesData' => $additionalImagesData,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type'], $attributes['size']),
        ]);
    }
}
