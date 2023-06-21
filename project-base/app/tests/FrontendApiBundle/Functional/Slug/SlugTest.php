<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Slug;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SlugTest extends GraphQlTestCase
{
    /**
     * @return iterable
     */
    public function getDataForSlugTest(): iterable
    {
        yield [
            'slug' => '/ostrava',
            'typename' => 'Store',
            'name' => 'Ostrava',
        ];

        yield [
            'slug' => '/21-5-hyundai-22mt44',
            'typename' => 'RegularProduct',
            'name' => '21,5” Hyundai 22MT44',
        ];

        yield [
            'slug' => '/27-hyundai-t27d590ey',
            'typename' => 'Variant',
            'name' => '27” Hyundai T27D590EY',
        ];

        yield [
            'slug' => '/32-hyundai-32pfl4400',
            'typename' => 'MainVariant',
            'name' => '32” Hyundai 32PFL4400',
        ];

        yield [
            'slug' => '/tv-audio',
            'typename' => 'Category',
            'name' => 'TV, audio',
        ];

        yield [
            'slug' => '/elektro-bez-hdmi-akce',
            'typename' => 'Category',
            'name' => 'Electronics',
        ];

        yield [
            'slug' => '/main-blog-page-en',
            'typename' => 'BlogCategory',
            'name' => 'Main blog page - %locale%',
            'parameters' => [
                '%locale%' => 'en',
            ],
        ];

        yield [
            'slug' => '/blog-article-example-37-en',
            'typename' => 'BlogArticle',
            'name' => 'Blog article example %counter% %locale%',
            'parameters' => [
                '%counter%' => '37',
                '%locale%' => 'en',
            ],
        ];

        yield [
            'slug' => '/brother',
            'typename' => 'Brand',
            'name' => 'Brother',
        ];

        yield [
            'slug' => '/made-in-de',
            'typename' => 'Flag',
            'name' => 'Made in DE',
        ];
    }

    /**
     * @dataProvider getDataForSlugTest
     * @param string $slug
     * @param string $typename
     * @param string $name
     * @param array $parameters
     */
    public function testSlug(string $slug, string $typename, string $name, array $parameters = []): void
    {
        $query = 'query slug {
    slug(slug: "' . $slug . '") {
        __typename
        name
    }
}';

        $jsonExpected = '{
    "data": {
        "slug": {
            "__typename": "' . $typename . '",
            "name": "' . t($name, $parameters, Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
