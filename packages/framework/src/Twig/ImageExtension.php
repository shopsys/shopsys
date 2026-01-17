<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    protected const string NOIMAGE_FILENAME = 'noimage.png';
    protected const array NON_HTML_ATTRIBUTES = [
        'type',
    ];

    protected string $frontDesignImageUrlPrefix;

    public function __construct(
        string $frontDesignImageUrlPrefix,
        protected readonly Domain $domain,
        protected readonly ImageLocator $imageLocator,
        protected readonly ImageFacade $imageFacade,
        protected readonly Environment $twigEnvironment,
        protected readonly ImageUrlWithSizeHelper $imageUrlWithSizeHelper,
    ) {
        $this->frontDesignImageUrlPrefix = rtrim($frontDesignImageUrlPrefix, '/');
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('image', $this->getImageHtml(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param object|\Shopsys\FrameworkBundle\Component\Image\Image $imageOrEntity
     */
    public function imageExists(object $imageOrEntity, ?string $type = null): bool
    {
        try {
            $image = $this->imageFacade->getImageByObject($imageOrEntity, $type);
        } catch (ImageNotFoundException $e) {
            return false;
        }

        return $this->imageLocator->imageExists($image);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     */
    protected function getImageUrl(object $imageOrEntity, array $attributes, DomainConfig $domainConfig): string
    {
        $width = null;
        $height = null;

        if (array_key_exists('width', $attributes)) {
            $width = (int)$attributes['width'];
        }

        if (array_key_exists('height', $attributes)) {
            $height = (int)$attributes['height'];
        }

        try {
            return $this->imageUrlWithSizeHelper->limitSizeInImageUrl($this->imageFacade->getImageUrl(
                $domainConfig,
                $imageOrEntity,
                $attributes['type'],
            ), $width, $height);
        } catch (ImageNotFoundException $e) {
            return $this->getEmptyImageUrl($domainConfig);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     */
    public function getImageHtml(
        object $imageOrEntity,
        array $attributes = [],
        int $domainId = Domain::FIRST_DOMAIN_ID,
    ): string {
        $this->preventDefault($attributes);

        $domainConfig = $this->domain->getDomainConfigById($domainId);

        try {
            $image = $this->imageFacade->getImageByObject($imageOrEntity, $attributes['type']);
            $entityName = $image->getEntityName();
            $attributes['src'] = $this->getImageUrl($image, $attributes, $domainConfig);
            $attributes['alt'] = $image->getName($domainConfig->getLocale());

            return $this->getImageHtmlByEntityName($attributes, $entityName);
        } catch (ImageNotFoundException $e) {
            return $this->getNoimageHtml($domainConfig, $attributes);
        }
    }

    protected function getNoimageHtml(DomainConfig $domainConfig, array $attributes = []): string
    {
        $this->preventDefault($attributes);

        $entityName = 'noimage';
        $attributes['src'] = $this->getEmptyImageUrl($domainConfig);

        return $this->getImageHtmlByEntityName($attributes, $entityName);
    }

    protected function getEmptyImageUrl(DomainConfig $domainConfig): string
    {
        return $domainConfig->getBaseUrl() . $this->frontDesignImageUrlPrefix . '/' . static::NOIMAGE_FILENAME;
    }

    protected function getImageCssClass(string $entityName, ?string $type): string
    {
        $allClassParts = [
            'image',
            $entityName,
            $type,
        ];
        $classParts = array_filter($allClassParts);

        return implode('-', $classParts);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_extension';
    }

    protected function preventDefault(array &$attributes): void
    {
        Utils::setArrayDefaultValue($attributes, 'type');
        Utils::setArrayDefaultValue($attributes, 'alt', '');
        Utils::setArrayDefaultValue($attributes, 'title', $attributes['alt']);
    }

    protected function getImageHtmlByEntityName(array $attributes, string $entityName): string
    {
        $htmlAttributes = $this->extractHtmlAttributesFromAttributes($attributes);

        return $this->twigEnvironment->render('@ShopsysFramework/Common/image.html.twig', [
            'attr' => $htmlAttributes,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type']),
        ]);
    }

    protected function extractHtmlAttributesFromAttributes(array $attributes): array
    {
        $htmlAttributes = $attributes;

        foreach (static::NON_HTML_ATTRIBUTES as $nonHtmlAttribute) {
            unset($htmlAttributes[$nonHtmlAttribute]);
        }

        return $htmlAttributes;
    }
}
