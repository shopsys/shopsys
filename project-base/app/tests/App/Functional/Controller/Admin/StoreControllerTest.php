<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class StoreControllerTest extends TransactionFunctionalTestCase
{
    public function testDeletingDefaultStoreFailsAndKeepsTheStore(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $administrationClientTestHelper = new AdministrationClientTestHelper($client);
        $defaultStoreId = $this->getReferenceForDomain(StoreDataFixture::STORE_FIRST, Domain::FIRST_DOMAIN_ID, Store::class)->getId();
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $administrationClientTestHelper->logInAdministrator($superadministrator->getId());
        $client->request('GET', $administrationClientTestHelper->generatePath('admin_crud_store_list'));

        $client->request('GET', $administrationClientTestHelper->generatePath('admin_crud_store_delete', [
            'id' => $defaultStoreId,
            RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $administrationClientTestHelper->getRouteCsrfTokenFromSessionOfLastRequest('admin_crud_store_delete'),
        ]));

        $this->assertResponseRedirects($administrationClientTestHelper->generatePath('admin_crud_store_list'));

        $client->followRedirect();

        $this->assertStringContainsString('An error occurred while deleting', (string)$client->getResponse()->getContent());
        $this->assertSame($defaultStoreId, $client->getContainer()->get(StoreFacade::class)->getById($defaultStoreId)->getId());
    }
}
