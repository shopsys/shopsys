<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Seo;

use Nette\Utils\Strings;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributes;

class SeoAttributesQueryDtoFactory
{
    public const int META_DESCRIPTION_MAX_LENGTH = 160;

    /**
     * @param string|null $fallbackDescriptionHtml used as the meta description when none is set in the administration
     */
    public function createFromSeoAttributes(
        SeoAttributes $seoAttributes,
        ?string $fallbackDescriptionHtml = null,
    ): SeoAttributesQueryDto {
        return $this->create(
            $seoAttributes->getTitle(),
            $seoAttributes->getMetaDescription(),
            $seoAttributes->getH1(),
            $seoAttributes->getMetaRobots(),
            $seoAttributes->getCanonicalUrl(),
            $fallbackDescriptionHtml,
        );
    }

    /**
     * @param string|null $fallbackDescriptionHtml used as the meta description when none is set in the administration
     */
    public function create(
        ?string $title,
        ?string $metaDescription,
        ?string $h1,
        ?string $metaRobots,
        ?string $canonicalUrl,
        ?string $fallbackDescriptionHtml = null,
    ): SeoAttributesQueryDto {
        return new SeoAttributesQueryDto(
            $title,
            $this->resolveMetaDescription($metaDescription, $fallbackDescriptionHtml),
            $h1,
            $metaRobots,
            $canonicalUrl,
        );
    }

    protected function resolveMetaDescription(?string $metaDescription, ?string $fallbackDescriptionHtml): ?string
    {
        $trimmedMetaDescription = TransformStringHelper::getTrimmedStringOrNullOnEmpty($metaDescription);

        if ($trimmedMetaDescription !== null) {
            return $trimmedMetaDescription;
        }

        $plainTextDescription = TransformStringHelper::getTrimmedStringOrNullOnEmpty(
            TransformStringHelper::convertHtmlToPlainText($fallbackDescriptionHtml),
        );

        if ($plainTextDescription === null) {
            return null;
        }

        return Strings::truncate($plainTextDescription, static::META_DESCRIPTION_MAX_LENGTH, '');
    }
}
