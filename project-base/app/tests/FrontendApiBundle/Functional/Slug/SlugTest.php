<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Slug;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SlugTest extends GraphQlTestCase
{
    /**
     * @return iterable
     */
    public static function getDataForSlugTest(): iterable
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
            'parameters' => [],
            'slug' => null,
            'useLocale' => true,
        ];

        yield [
            'typename' => 'BlogArticle',
            'name' => 'Blog article example %counter% %locale%',
            'parameters' => [
                '%counter%' => '37',
            ],
            'slug' => null,
            'useLocale' => true,
        ];

        yield [
            'typename' => 'Brand',
            'name' => 'Brother',
        ];

        yield [
            'typename' => 'Flag',
            'name' => 'Made in DE',
            'parameters' => [],
            'slug' => null,
            'useLocale' => false,
            'translationDomain' => Translator::DEFAULT_TRANSLATION_DOMAIN,
        ];
    }

    /**
     * @param string $typename
     * @param string $name
     * @param array $parameters
     * @param string|null $slug
     * @param bool|null $useLocale
     * @param string|null $translationDomain
     */
    #[DataProvider('getDataForSlugTest')]
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
