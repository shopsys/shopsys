<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Zbozi;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CategoryZboziCategoryTest extends GraphQlTestCase
{
    public function testCategoryZboziCategoryIsNullOnFirstDomain(): void
    {
        if ($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale() === 'cs') {
            $this->markTestSkipped('First domain has cs locale where zbozi mappings exist; this test expects no mapping.');
        }

        $electronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryZboziCategoryQuery.graphql', [
            'categoryUuid' => $electronics->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertNull($data['zboziCategory']);
    }

    public function testCategoryZboziCategoryReturnsFullNameOnCsDomain(): void
    {
        if ($this->domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID)->getLocale() !== 'cs') {
            $this->markTestSkipped('Second domain is not in cs locale; zbozi mappings are seeded only for cs.');
        }

        $this->domain->switchDomainById(Domain::SECOND_DOMAIN_ID);

        $electronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryZboziCategoryQuery.graphql', [
            'categoryUuid' => $electronics->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame('Foto | Foto doplňky a příslušenství | Blesky', $data['zboziCategory']);
    }
}
