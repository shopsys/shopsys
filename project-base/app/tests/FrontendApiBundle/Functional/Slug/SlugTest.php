<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Slug;

use Shopsys\FrameworkBundle\Component\String\TransformString;
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
            'typename' => 'Store',
            'name' => 'Ostrava',
        ];

        yield [
            'typename' => 'RegularProduct',
            'name' => '21,5" Hyundai 22MT44',
        ];

        yield [
            'typename' => 'Variant',
            'name' => '27" Hyundai T27D590EY',
        ];

        yield [
            'typename' => 'MainVariant',
            'name' => '32" Hyundai 32PFL4400',
        ];

        yield [
            'typename' => 'Category',
            'name' => 'TV, audio',
        ];

        yield [
            'typename' => 'Category',
            'name' => 'Electronics',
            'parameters' => [],
            'slug' => '/elektro-bez-hdmi-akce',
        ];

        yield [
            'typename' => 'BlogCategory',
            'name' => 'Main blog page - %locale%',
            [],
            null,
            true,
        ];

        yield [
            'typename' => 'BlogArticle',
            'name' => 'Blog article example %counter% %locale%',
            'parameters' => [
                '%counter%' => '37',
            ],
            null,
            true,
        ];

        yield [
            'typename' => 'Brand',
            'name' => 'Brother',
        ];

        yield [
            'typename' => 'Flag',
            'name' => 'Made in DE',
            [],
            null,
            false,
            Translator::DEFAULT_TRANSLATION_DOMAIN,
        ];
    }

    /**
     * @dataProvider getDataForSlugTest
     * @param string $typename
     * @param string $name
     * @param mixed[] $parameters
     * @param string|null $slug
     * @param bool|null $useLocale
     * @param string|null $translationDomain
     */
    public function testSlug(
        string $typename,
        string $name,
        array $parameters = [],
        ?string $slug = null,
        ?bool $useLocale = false,
        ?string $translationDomain = Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
    ): void {
        if ($useLocale === true) {
            $parameters['%locale%'] = $this->getFirstDomainLocale();
        }

        $translatedName = t($name, $parameters, $translationDomain, $this->getFirstDomainLocale());

        if ($slug === null) {
            $slug = '/' . TransformString::stringToFriendlyUrlSlug($translatedName);
        }

        $escapedName = str_replace('"', '\\"', $translatedName);

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
            "name": "' . $escapedName . '"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
