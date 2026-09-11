<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\PriceListDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\Test\TransactionFunctionalTestCase;

final class PriceListControllerTest extends TransactionFunctionalTestCase
{
    public function testListShowsValidityStatusOfEachPriceList(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $administrationClientTestHelper = new AdministrationClientTestHelper($client);
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $administrationClientTestHelper->logInAdministrator($superadministrator->getId());

        $crawler = $client->request('GET', $administrationClientTestHelper->generatePath('admin_crud_price_list_list'));

        $this->assertResponseStatusCodeSame(200);

        $expectedStatusesIndexedByPriceListReference = [
            PriceListDataFixture::ACTIVE_ITEMS_ON_SALE_REFERENCE => 'Active',
            PriceListDataFixture::FUTURE_PROMOTED_PRODUCTS_REFERENCE => 'Future',
            PriceListDataFixture::EXPIRED_BLUE_WEDNESDAY_REFERENCE => 'Expired',
        ];

        foreach ($expectedStatusesIndexedByPriceListReference as $priceListReference => $expectedStatus) {
            $priceList = $this->getReferenceForDomain($priceListReference, Domain::FIRST_DOMAIN_ID, PriceList::class);
            $editPath = $administrationClientTestHelper->generatePath('admin_crud_price_list_edit', ['id' => $priceList->getId()]);
            $priceListRow = $crawler
                ->filter('tr')
                ->reduce(static fn (Crawler $row): bool => $row->filter(sprintf('a[href$="%s"]', $editPath))->count() > 0);

            $this->assertSame($expectedStatus, trim($priceListRow->filter('.status')->text()), $priceListReference);
        }
    }
}
