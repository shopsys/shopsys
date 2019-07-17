<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Smoke;

use Tests\ShopBundle\Test\OauthTestCase;

/**
 * This test must not extend TransactionFunctionalTestCase because it mustn't use transaction
 * If it is run in transaction, tokens don't work and test fails
 */
class BackendApiCreateProductTest extends OauthTestCase
{
    public function testCreatePostProduct(): void
    {
        $product = [
            'name' => [
                'en' => 'X tech',
                'cs' => 'název',
            ],
            'hidden' => true,
            'sellingDenied' => true,
            'sellingFrom' => '2019-07-16T00:00:00+00:00',
            'sellingTo' => '2020-07-16T00:00:00+00:00',
            'catnum' => '123456 co',
            'ean' => 'E12346B',
            'partno' => 'P123456',
            'shortDescription' => [
                1 => '<b>desc',
                2 => '<b>popisek',
            ],
            'longDescription' => [
                1 => '<b>desc long',
                2 => '<b>popisek dlouhy',
            ],
        ];
        $response = $this->runOauthRequest('POST', '/api/v1/products', $product);

        $this->assertSame(201, $response->getStatusCode());

        $location = $response->headers->get('Location');
        $this->assertProductLocation($location);

        $uuid = $this->extractUuid($location);

        $foundProduct = $this->getProduct($uuid);
        $this->assertSame($product, $foundProduct);
    }

    public function testCreatePostMinimalProduct(): void
    {
        $product = [
            'name' => [
                'en' => 'X tech',
                'cs' => 'název',
            ],
            'hidden' => false,
            'sellingDenied' => false,
            'sellingFrom' => null,
            'sellingTo' => null,
            'catnum' => '123456 co',
            'ean' => 'E12346B',
            'partno' => 'P123456',
            'shortDescription' => [
                1 => '<b>desc',
                2 => '<b>popisek',
            ],
            'longDescription' => [
                1 => '<b>desc long',
                2 => '<b>popisek dlouhy',
            ],
        ];
        $response = $this->runOauthRequest('POST', '/api/v1/products', $product);

        $this->assertSame(201, $response->getStatusCode());

        $location = $response->headers->get('Location');
        $this->assertProductLocation($location);

        $uuid = $this->extractUuid($location);

        $foundProduct = $this->getProduct($uuid);
        $this->assertSame($product, $foundProduct);
    }

    /**
     * @param string $location
     */
    private function assertProductLocation(string $location): void
    {
        $expectedLocationStart = sprintf('%s/api/v1/products/', $this->getDomainBaseUrl());
        $this->assertStringStartsWith($expectedLocationStart, $location);
    }

    /**
     * @param string $uuid
     * @return array
     */
    private function getProduct(string $uuid): array
    {
        $response = $this->runOauthRequest('GET', '/api/v1/products/' . $uuid);
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        unset($data['uuid']);
        return $data;
    }
}
