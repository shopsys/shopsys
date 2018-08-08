<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class ImageExtension extends Twig_Extension
{
    const NOIMAGE_FILENAME = 'noimage.png';

    /**
     * @var string
     */
    private $frontDesignImageUrlPrefix;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageLocator
     */
    private $imageLocator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    public function __construct(
        string $frontDesignImageUrlPrefix,
        ContainerInterface $container,
        Domain $domain,
        ImageLocator $imageLocator,
        ImageFacade $imageFacade,
        EngineInterface $templating
    ) {
        $this->frontDesignImageUrlPrefix = $frontDesignImageUrlPrefix;
        $this->container = $container;
        $this->domain = $domain;
        $this->imageLocator = $imageLocator;
        $this->imageFacade = $imageFacade;
        $this->templating = $templating;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('imageExists', [$this, 'imageExists']),
            new Twig_SimpleFunction('imageUrl', [$this, 'getImageUrl']),
            new Twig_SimpleFunction('image', [$this, 'getImageHtml'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('noimage', [$this, 'getNoimageHtml'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('getImages', [$this, 'getImages']),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param string|null $type
     */
    public function imageExists($imageOrEntity, $type = null): bool
    {
        try {
            $image = $this->imageFacade->getImageByObject($imageOrEntity, $type);
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
            return false;
        }

        return $this->imageLocator->imageExists($image);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|Object $imageOrEntity
     * @param string|null $sizeName
     * @param string|null $type
     */
    public function getImageUrl($imageOrEntity, $sizeName = null, $type = null): string
    {
        try {
            return $this->imageFacade->getImageUrl($this->domain->getCurrentDomainConfig(), $imageOrEntity, $sizeName, $type);
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
            return $this->getEmptyImageUrl();
        }
    }

    /**
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImages(Object $entity, ?string $type = null): array
    {
        return $this->imageFacade->getImagesByEntityIndexedById($entity, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|Object $imageOrEntity
     */
    public function getImageHtml($imageOrEntity, array $attributes = []): string
    {
        $this->preventDefault($attributes);

        try {
            $image = $this->imageFacade->getImageByObject($imageOrEntity, $attributes['type']);
            $entityName = $image->getEntityName();
            $attributes['src'] = $this->getImageUrl($image, $attributes['size'], $attributes['type']);
        } catch (\Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException $e) {
            $entityName = 'noimage';
            $attributes['src'] = $this->getEmptyImageUrl();
        }

        return $this->getImageHtmlByEntityName($attributes, $entityName);
    }

    public function getNoimageHtml(array $attributes = []): string
    {
        $this->preventDefault($attributes);

        $entityName = 'noimage';
        $attributes['src'] = $this->getEmptyImageUrl();

        return $this->getImageHtmlByEntityName($attributes, $entityName);
    }

    private function getEmptyImageUrl(): string
    {
        return $this->domain->getUrl() . $this->frontDesignImageUrlPrefix . self::NOIMAGE_FILENAME;
    }

    private function getImageCssClass(string $entityName, ?string $type, ?string $sizeName): string
    {
        $allClassParts = [
            'image',
            $entityName,
            $type,
            $sizeName,
        ];
        $classParts = array_filter($allClassParts);

        return implode('-', $classParts);
    }

    public function getName(): string
    {
        return 'image_extension';
    }

    private function preventDefault(array &$attributes): void
    {
        Utils::setArrayDefaultValue($attributes, 'type');
        Utils::setArrayDefaultValue($attributes, 'size');
        Utils::setArrayDefaultValue($attributes, 'alt', '');
        Utils::setArrayDefaultValue($attributes, 'title', $attributes['alt']);
    }

    private function getImageHtmlByEntityName(array $attributes, $entityName): string
    {
        $htmlAttributes = $attributes;
        unset($htmlAttributes['type'], $htmlAttributes['size']);

        return $this->templating->render('@ShopsysFramework/Common/image.html.twig', [
            'attr' => $htmlAttributes,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type'], $attributes['size']),
        ]);
    }
}
