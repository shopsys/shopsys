<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\AutocompleteFavorites;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AutocompleteFavoritesTest extends GraphQlTestCase
{
    public function testAutocompleteFavoritesQuery(): void
    {
        $firstExpectedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $firstExpectedBrand = $this->getReference(BrandDataFixture::BRAND_APPLE, Brand::class);
        $firstExpectedCategory = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AutocompleteFavoritesQuery.graphql');
        $favoritesData = $this->getResponseDataForGraphQlType($response, 'autocompleteFavorites');

        $this->assertArrayHasKey('products', $favoritesData);
        $this->assertArrayHasKey('categories', $favoritesData);
        $this->assertArrayHasKey('brands', $favoritesData);

        $this->assertIsArray($favoritesData['products']);
        $this->assertCount(3, $favoritesData['products']);
        $this->assertIsArray($favoritesData['categories']);
        $this->assertCount(3, $favoritesData['categories']);
        $this->assertIsArray($favoritesData['brands']);
        $this->assertCount(3, $favoritesData['brands']);

        $this->assertArrayHasKey('name', $favoritesData['products'][0]);
        $this->assertSame($firstExpectedProduct->getName($this->getFirstDomainLocale()), $favoritesData['products'][0]['name']);

        $this->assertArrayHasKey('name', $favoritesData['categories'][0]);
        $this->assertSame($firstExpectedCategory->getName($this->getFirstDomainLocale()), $favoritesData['categories'][0]['name']);

        $this->assertArrayHasKey('name', $favoritesData['brands'][0]);
        $this->assertSame($firstExpectedBrand->getName(), $favoritesData['brands'][0]['name']);
    }

    public function testAutocompleteFavoritesQueryReturnsListableProductsOnly(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AutocompleteFavoritesQuery.graphql');
        $favoritesData = $this->getResponseDataForGraphQlType($response, 'autocompleteFavorites');

        $firstDomainLocale = $this->getFirstDomainLocale();

        /**
         * despite these products are set in @see \App\DataFixtures\Demo\AutocompleteFavoriteDataFixture,
         * they should not be returned by the query
         */
        $unexpectedProductNames = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148', Product::class)->getName($firstDomainLocale),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '76', Product::class)->getName($firstDomainLocale),
        ];

        $productNames = array_column($favoritesData['products'], 'name');

        foreach ($unexpectedProductNames as $unexpectedProductName) {
            $this->assertNotContains($unexpectedProductName, $productNames);
        }
    }
}
