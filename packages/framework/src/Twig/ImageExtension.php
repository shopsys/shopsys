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
use Twig\TwigFilter;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    protected const string NOIMAGE_FILENAME = 'noimage.png';
    protected const array NON_HTML_ATTRIBUTES = [
        'type',
    ];
    protected const string CDN_NO_CONVERT_QUERY_PARAM = 'vshcdn-webp-noautoconvert=1';

    protected string $frontDesignImageUrlPrefix;

    /**
     * @param string $frontDesignImageUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Twig\Environment $twigEnvironment
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper $imageUrlWithSizeHelper
     */
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
            new TwigFunction('imageForEmail', $this->getImageHtmlForEmail(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('escapeButAmp', [$this, 'escapeButAmp'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Escapes HTML special characters but keeps ampersands unescaped for email compatibility.
     * This is needed because Outlook email clients require unescaped ampersands in URLs.
     *
     * @param string|null $string
     * @return string
     */
    public function escapeButAmp(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        $escapedString = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return str_replace('&amp;', '&', $escapedString);
    }

    /**
     * @param object|\Shopsys\FrameworkBundle\Component\Image\Image $imageOrEntity
     * @param string|null $type
     * @return bool
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
     * @param array $attributes
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
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
     * @param array $attributes
     * @param int $domainId
     * @return string
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

    /**
     * Returns image HTML optimized for email clients, including Microsoft Outlook.
     * Uses conditional comments to serve original (non-WebP) images to Outlook clients
     * which don't support WebP format.
     *
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|object $imageOrEntity
     * @param array $attributes
     * @param int $domainId
     * @return string
     */
    public function getImageHtmlForEmail(
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

            return $this->getImageHtmlByEntityNameForEmail($attributes, $entityName);
        } catch (ImageNotFoundException $e) {
            return $this->getNoimageHtmlForEmail($domainConfig, $attributes);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param array $attributes
     * @return string
     */
    protected function getNoimageHtmlForEmail(DomainConfig $domainConfig, array $attributes = []): string
    {
        $this->preventDefault($attributes);

        $entityName = 'noimage';
        $attributes['src'] = $this->getEmptyImageUrl($domainConfig) . '?';

        return $this->getImageHtmlByEntityNameForEmail($attributes, $entityName);
    }

    /**
     * @param array $attributes
     * @param string $entityName
     * @return string
     */
    protected function getImageHtmlByEntityNameForEmail(array $attributes, string $entityName): string
    {
        $htmlAttributes = $this->extractHtmlAttributesFromAttributes($attributes);

        $htmlAttributesForMs = $htmlAttributes;
        $separator = str_contains($htmlAttributesForMs['src'], '?') ? '&' : '?';
        $htmlAttributesForMs['src'] = $htmlAttributesForMs['src'] . $separator . static::CDN_NO_CONVERT_QUERY_PARAM;

        return $this->twigEnvironment->render('@ShopsysFramework/Common/imageEmail.html.twig', [
            'attr' => $htmlAttributes,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type']),
            'attrForMs' => $htmlAttributesForMs,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param array $attributes
     * @return string
     */
    protected function getNoimageHtml(DomainConfig $domainConfig, array $attributes = []): string
    {
        $this->preventDefault($attributes);

        $entityName = 'noimage';
        $attributes['src'] = $this->getEmptyImageUrl($domainConfig);

        return $this->getImageHtmlByEntityName($attributes, $entityName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    protected function getEmptyImageUrl(DomainConfig $domainConfig): string
    {
        return $domainConfig->getBaseUrl() . $this->frontDesignImageUrlPrefix . '/' . static::NOIMAGE_FILENAME;
    }

    /**
     * @param string $entityName
     * @param string|null $type
     * @return string
     */
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

    /**
     * @param array $attributes
     */
    protected function preventDefault(array &$attributes): void
    {
        Utils::setArrayDefaultValue($attributes, 'type');
        Utils::setArrayDefaultValue($attributes, 'alt', '');
        Utils::setArrayDefaultValue($attributes, 'title', $attributes['alt']);
    }

    /**
     * @param array $attributes
     * @param string $entityName
     * @return string
     */
    protected function getImageHtmlByEntityName(array $attributes, string $entityName): string
    {
        $htmlAttributes = $this->extractHtmlAttributesFromAttributes($attributes);

        return $this->twigEnvironment->render('@ShopsysFramework/Common/image.html.twig', [
            'attr' => $htmlAttributes,
            'imageCssClass' => $this->getImageCssClass($entityName, $attributes['type']),
        ]);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function extractHtmlAttributesFromAttributes(array $attributes): array
    {
        $htmlAttributes = $attributes;

        foreach (static::NON_HTML_ATTRIBUTES as $nonHtmlAttribute) {
            unset($htmlAttributes[$nonHtmlAttribute]);
        }

        return $htmlAttributes;
    }
}
