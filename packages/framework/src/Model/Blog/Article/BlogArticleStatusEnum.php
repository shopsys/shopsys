<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class BlogArticleStatusEnum extends AbstractEnum
{
    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_PREVIEW = 'preview';
    public const string STATUS_PUBLISHED = 'published';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Draft') => static::STATUS_DRAFT,
            t('Preview') => static::STATUS_PREVIEW,
            t('Published') => static::STATUS_PUBLISHED,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getStatusDescriptions(?string $previewUrl): array
    {
        $previewDescription = t('Article is accessible via direct URL only, with noindex/nofollow.');

        if ($previewUrl !== null) {
            $previewDescription .= ' <a href="' . htmlspecialchars($previewUrl) . '" target="_blank">' . htmlspecialchars($previewUrl) . '</a>';
        }

        return [
            static::STATUS_DRAFT => t('Article is only visible in administration.'),
            static::STATUS_PREVIEW => $previewDescription,
            static::STATUS_PUBLISHED => t('Article is ready to be displayed on the storefront.'),
        ];
    }
}
