<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use App\DataFixtures\Demo\AdministratorDataFixture;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The domain control of a listing has to reach the database — the domain selected in the session of the
 * administrator narrows the listing, and a listing restricted to some domains never lists a record of another one.
 */
class DomainControlNarrowingTest extends TransactionFunctionalTestCase
{
    private const int FIRST_DOMAIN_ID = 1;
    private const int SECOND_DOMAIN_ID = 2;

    private const string FILTER_NAMESPACE = 'crud_test';

    /**
     * @inject
     */
    private OrmAdapterFactory $ormAdapterFactory;

    /**
     * @inject
     */
    private DomainControlScopeFactory $domainControlScopeFactory;

    /**
     * @inject
     */
    private AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * The adapter resolves the locale of the translated fields from the current request, the same way it
     * does when a listing is rendered.
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();

        // the domains the administrator works with decide what the domain control offers
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->tokenStorage->setToken(null);

        parent::tearDown();
    }

    public function testDomainSelectedInSessionNarrowsTheListing(): void
    {
        $this->adminDomainFilterTabsFacade->setSelectedDomainId(self::FILTER_NAMESPACE, self::SECOND_DOMAIN_ID);

        $domainControlScope = $this->domainControlScopeFactory->create(
            new DomainControlConfig(DomainControlType::FILTER, null, self::FILTER_NAMESPACE),
            NewsletterSubscriber::class,
        );

        $this->assertNotNull($domainControlScope);
        $this->assertSame(self::SECOND_DOMAIN_ID, $domainControlScope->selectedDomainId);
        $this->assertSame([self::SECOND_DOMAIN_ID], array_unique($this->getDomainIdsOfListedSubscribersOf($domainControlScope)));
    }

    /**
     * A selection outside the domains the listing is restricted to is cleared, the listing then covers every domain
     * it is restricted to and none other.
     */
    public function testListingRestrictedToDomainsNeverListsAnotherOne(): void
    {
        $this->adminDomainFilterTabsFacade->setSelectedDomainId(self::FILTER_NAMESPACE, self::SECOND_DOMAIN_ID);

        $domainControlScope = $this->domainControlScopeFactory->create(
            new DomainControlConfig(DomainControlType::FILTER, [self::FIRST_DOMAIN_ID], self::FILTER_NAMESPACE),
            NewsletterSubscriber::class,
        );

        $this->assertNotNull($domainControlScope);
        $this->assertNull($domainControlScope->selectedDomainId);
        $this->assertSame([self::FIRST_DOMAIN_ID], array_unique($this->getDomainIdsOfListedSubscribersOf($domainControlScope)));
    }

    public function testListingIsNarrowedToTheSelectedDomain(): void
    {
        $subscribersOfFirstDomain = $this->getDomainIdsOfListedSubscribers(self::FIRST_DOMAIN_ID);
        $subscribersOfSecondDomain = $this->getDomainIdsOfListedSubscribers(self::SECOND_DOMAIN_ID);

        $this->assertNotEmpty($subscribersOfFirstDomain, 'The demo data have to contain subscribers of the first domain.');
        $this->assertNotEmpty($subscribersOfSecondDomain, 'The demo data have to contain subscribers of the second domain.');
        $this->assertSame([self::FIRST_DOMAIN_ID], array_unique($subscribersOfFirstDomain));
        $this->assertSame([self::SECOND_DOMAIN_ID], array_unique($subscribersOfSecondDomain));
    }

    public function testListingWithoutDomainControlCoversEveryDomain(): void
    {
        $adapter = $this->ormAdapterFactory->create(NewsletterSubscriber::class);

        $this->assertGreaterThan(
            1,
            count(array_unique($this->getDomainIdsOfRows($adapter->getDatasource(new DatasourceRequest('id', [new FieldDescriptor('domainId')]))))),
        );
    }

    /**
     * @return int[]
     */
    private function getDomainIdsOfListedSubscribers(int $selectedDomainId): array
    {
        return $this->getDomainIdsOfListedSubscribersOf(new DomainControlScope(
            DomainControlType::FILTER,
            [self::FIRST_DOMAIN_ID, self::SECOND_DOMAIN_ID],
            $selectedDomainId,
            self::FILTER_NAMESPACE,
            'domainId',
        ));
    }

    /**
     * @return int[]
     */
    private function getDomainIdsOfListedSubscribersOf(DomainControlScope $domainControlScope): array
    {
        $adapter = $this->ormAdapterFactory->create(NewsletterSubscriber::class);
        $domainIds = $this->getDomainIdsOfRows($adapter->getDatasource(
            new DatasourceRequest('id', [new FieldDescriptor('domainId')], $domainControlScope->createCondition()),
        ));
        $this->assertNotEmpty($domainIds, 'The demo data have to contain subscribers of the listed domains.');

        return $domainIds;
    }

    /**
     * @return int[]
     */
    private function getDomainIdsOfRows(DataSourceInterface $dataSource): array
    {
        return array_map(
            static fn (array $row): int => (int)$row['domainId'],
            $dataSource->getPaginatedRows()->getResults(),
        );
    }
}
