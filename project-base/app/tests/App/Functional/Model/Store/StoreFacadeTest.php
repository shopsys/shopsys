<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Store;

use App\DataFixtures\Demo\StoreDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Store\Exception\DefaultStoreCannotBeDeletedException;
use Shopsys\FrameworkBundle\Model\Store\Exception\StoreNotFoundException;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class StoreFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    protected StoreFacade $storeFacade;

    public function testDefaultStoreCannotBeDeleted(): void
    {
        $defaultStore = $this->getReferenceForDomain(StoreDataFixture::STORE_FIRST, Domain::FIRST_DOMAIN_ID, Store::class);
        $this->assertTrue($defaultStore->isDefault());

        $this->expectException(DefaultStoreCannotBeDeletedException::class);
        $this->storeFacade->delete($defaultStore->getId());
    }

    public function testOtherStoreCanBeDeleted(): void
    {
        $store = $this->getReferenceForDomain(StoreDataFixture::STORE_SECOND, Domain::FIRST_DOMAIN_ID, Store::class);
        $this->assertFalse($store->isDefault());
        $storeId = $store->getId();

        $this->storeFacade->delete($storeId);

        $this->expectException(StoreNotFoundException::class);
        $this->storeFacade->getById($storeId);
    }
}
