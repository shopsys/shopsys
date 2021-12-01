<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Slug;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SlugTest extends GraphQlTestCase
{
    public function getDataForSlugTest()
    {
        return [
            ['slug' => '/ostrava', 'typename' => 'Store', 'name' => 'Ostrava'],
            ['slug' => '/21-5-hyundai-22mt44', 'typename' => 'RegularProduct', 'name' => '21,5” Hyundai 22MT44'],
            ['slug' => '/27-hyundai-t27d590ey', 'typename' => 'Variant', 'name' => '27” Hyundai T27D590EY'],
            ['slug' => '/hyundai-32pfl4400', 'typename' => 'MainVariant', 'name' => 'Hyundai 32PFL4400'],
            ['slug' => '/televize-audio', 'typename' => 'Category', 'name' => 'Televize, audio'],
            ['slug' => '/elektro-bez-hdmi', 'typename' => 'Category', 'name' => 'Elektro'],
            ['slug' => '/hlavni-stranka-blogu-cs', 'typename' => 'BlogCategory', 'name' => 'Hlavní stránka blogu - cs'],
            ['slug' => '/ukazkovy-clanek-blogu-37-cs', 'typename' => 'BlogArticle', 'name' => 'Ukázkový článek blogu 37 cs'],
            ['slug' => '/brother', 'typename' => 'Brand', 'name' => 'Brother'],
            ['slug' => '/vyrobeno-v-de', 'typename' => 'Flag', 'name' => 'Vyrobeno v DE'],
        ];
    }

    /**
     * @dataProvider getDataForSlugTest
     * @param string $slug
     * @param string $typename
     * @param string $name
     */
    public function testSlug(string $slug, string $typename, string $name): void
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
            "name": "' . $name . '"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
