<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MultipleProductsQueryTest extends GraphQlTestCase
{
    public function testMultipleProductsQueriesAtOnce(): void
    {
        $query = 'query slug {
  slug(slug: "televize-audio") {
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
      "name": "Televize, audio",
      "products": {
        "edges": [
          {
            "node": {
              "name": "' . t('Samsung UE75HU7500 (UHD)', [], 'dataFixtures', $firstDomainLocale) . '"
            }
          },
          {
            "node": {
              "name": "' . t('LG 47LA790W (FHD)', [], 'dataFixtures', $firstDomainLocale) . '"
            }
          }
        ]
      },
      "productsStatic": {
        "edges": [
          {
            "node": {
              "name": "' . t('Samsung T27D590EW', [], 'dataFixtures', $firstDomainLocale) . '"
            }
          },
          {
            "node": {
              "name": "' . t('Samsung T27D590EX', [], 'dataFixtures', $firstDomainLocale) . '"
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
