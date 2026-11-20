<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Override;

class BlogArticleChangeLabel implements TranslationContainerInterface
{
    public const string SUBJECT = 'Blog article #%id% "%name%"';
    public const string NAME = 'Name (%locale%)';
    public const string PEREX = 'Perex (%locale%)';
    public const string CONTENT = 'Content (%locale%)';
    public const string CATEGORIES = 'Categories (domain %domainId%)';
    public const string SLUG = 'Slug (domain %domainId%)';
    public const array SEO_BY_ATTRIBUTE = [
        'title' => 'SEO title (domain %domainId%)',
        'metaDescription' => 'SEO meta description (domain %domainId%)',
        'h1' => 'SEO H1 (domain %domainId%)',
    ];

    /**
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    #[Override]
    public static function getTranslationMessages(): array
    {
        return array_map(
            static fn (string $label) => new Message($label),
            [
                static::SUBJECT,
                static::NAME,
                static::PEREX,
                static::CONTENT,
                static::CATEGORIES,
                static::SLUG,
                ...array_values(static::SEO_BY_ATTRIBUTE),
            ],
        );
    }
}
