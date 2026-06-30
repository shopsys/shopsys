<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Zbozi;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CategoryZboziCategoryTest extends GraphQlTestCase
{
    public function testCategoryZboziCategoryIsNullOnNonCsDomain(): void
    {
        $domainId = ZboziDomainTestHelper::findFirstNonCsDomainId($this->domain);

        if ($domainId === null) {
            $this->markTestSkipped('There is no non-cs domain where zbozi mappings are expected to be missing.');
        }

        $this->domain->switchDomainById($domainId);

        $electronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryZboziCategoryQuery.graphql', [
            'categoryUuid' => $electronics->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertNull($data['zboziCategory']);
    }

    public function testCategoryZboziCategoryReturnsFullNameOnCsDomain(): void
    {
        $domainId = ZboziDomainTestHelper::findFirstCsDomainId($this->domain);

        if ($domainId === null) {
            $this->markTestSkipped('There is no cs domain where zbozi mappings are seeded.');
        }

        $this->domain->switchDomainById($domainId);

        $electronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryZboziCategoryQuery.graphql', [
            'categoryUuid' => $electronics->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame('Foto | Foto doplňky a příslušenství | Blesky', $data['zboziCategory']);
    }
}
