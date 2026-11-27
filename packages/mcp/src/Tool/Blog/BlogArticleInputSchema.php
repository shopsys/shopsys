<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

class BlogArticleInputSchema
{
    public const string NAMES = 'Names keyed by locale, for example {"cs": "Jak vybrat televizi", "en": "How to choose a TV"}.';
    public const string PEREXES = 'Short plain-text summaries keyed by locale.';
    public const string DESCRIPTIONS = 'Article content keyed by locale, written in HTML. It is stored as it is and administrators edit it in a visual editor. Do not repeat the name as a heading. An empty string removes the content.';
    public const string DESCRIPTIONS_REPLACE_ALL = 'The given content replaces the whole current content including its styles and components, use descriptionReplacements to change only a part of it.';
    public const string DESCRIPTION_REPLACEMENTS = 'Changes of the current HTML content keyed by locale, for example {"en": [{"find": "<p>Old sentence.</p>", "replace": "<p>New sentence.</p>"}]}. The replacements are applied in the given order, each "find" must be an exact part of the stored HTML that occurs in it exactly once. The rest of the content including styles and components is kept. Read the current content first, for example SELECT locale, description FROM blog_article_translations WHERE translatable_id = 1.';
    public const string BLOG_CATEGORY_IDS_BY_DOMAIN_ID = 'IDs of blog categories keyed by domain ID, for example {"1": [2, 3]}. The given list replaces all categories of the article on the domain.';
    public const string SEO_BY_DOMAIN_ID = 'SEO attributes keyed by domain ID, for example {"1": {"title": "...", "metaDescription": "...", "h1": "..."}}. Only the given attributes are changed.';
    public const string SLUGS_BY_DOMAIN_ID = 'URL slugs keyed by domain ID, for example {"1": "how-to-choose-a-tv"}. The slug becomes the main URL of the article on the domain, the previous URL redirects to it.';
    public const string UNCHANGED_WHEN_OMITTED = 'Omit to keep the current values, locales or domains that are not listed are kept as well.';

    public const array NAME = [
        'type' => 'string',
        'minLength' => 1,
        'maxLength' => 255,
    ];

    public const array SLUG = [
        'type' => 'string',
        'minLength' => 1,
        'maxLength' => 255,
    ];

    public const array BLOG_CATEGORY_IDS = [
        'type' => 'array',
        'items' => [
            'type' => 'integer',
        ],
    ];

    public const array DESCRIPTION_REPLACEMENT_LIST = [
        'type' => 'array',
        'minItems' => 1,
        'items' => [
            'type' => 'object',
            'properties' => [
                'find' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'replace' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['find', 'replace'],
            'additionalProperties' => false,
        ],
    ];

    public const array SEO = [
        'type' => 'object',
        'properties' => [
            'title' => ['type' => ['string', 'null']],
            'metaDescription' => ['type' => ['string', 'null']],
            'h1' => ['type' => ['string', 'null']],
        ],
        'additionalProperties' => false,
    ];
}
