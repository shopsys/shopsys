<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Zbozi;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductZboziCategoryTest extends GraphQlTestCase
{
    public function testProductZboziCategoryIsNullOnNonCsDomain(): void
    {
        $domainId = ZboziDomainTestHelper::findFirstNonCsDomainId($this->domain);

        if ($domainId === null) {
            $this->markTestSkipped('There is no non-cs domain where zbozi mappings are expected to be missing.');
        }

        $this->domain->switchDomainById($domainId);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductZboziCategoryQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $this->assertNull($data['zboziCategory']);
    }

    public function testProductZboziCategoryReturnsFullNameOnCsDomain(): void
    {
        $domainId = ZboziDomainTestHelper::findFirstCsDomainId($this->domain);

        if ($domainId === null) {
            $this->markTestSkipped('There is no cs domain where zbozi mappings are seeded.');
        }

        $this->domain->switchDomainById($domainId);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductZboziCategoryQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'product');

        $this->assertSame('Foto | Foto doplňky a příslušenství | Objektivy', $data['zboziCategory']);
    }
}
