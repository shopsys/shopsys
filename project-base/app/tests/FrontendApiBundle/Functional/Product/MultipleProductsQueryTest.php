<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MultipleProductsQueryTest extends GraphQlTestCase
{
    public function testMultipleProductsQueriesAtOnce(): void
    {
        $query = 'query slug {
  slug(slug: "tv-audio") {
    ... on Category {
      name
      products(first: 2, orderingMode: PRICE_DESC) {
        edges {
          node {
            name
          }
        }
      }      
      productsStatic: products(first: 2, filter: {minimalPrice: "6000"}, orderingMode: PRICE_ASC, search: "Samsung") {
        edges {
          node {
            name
          }
        }
      }
    }
  }
}';
        $firstDomainLocale = $this->getFirstDomainLocale();
        $expectedResult = '{
  "data": {
    "slug": {
      "name": "TV, audio",
      "products": {
        "edges": [
          {
            "node": {
              "name": "' . t('Samsung UE75HU7500 (UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . '"
            }
          },
          {
            "node": {
              "name": "' . t('LG 47LA790W (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . '"
            }
          }
        ]
      },
      "productsStatic": {
        "edges": [
          {
            "node": {
              "name": "' . t('Samsung UE75HU7500 (UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . '"
            }
          }
        ]
      }
    }
  }
}';

        $this->assertQueryWithExpectedJson($query, $expectedResult);
    }
}
