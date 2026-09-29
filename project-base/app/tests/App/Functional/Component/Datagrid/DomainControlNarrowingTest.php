<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\OrmAdapterFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The domain control of a listing has to reach the database — an administrator restricted to a domain
 * must never see a record of another one.
 */
class DomainControlNarrowingTest extends TransactionFunctionalTestCase
{
    private const int FIRST_DOMAIN_ID = 1;
    private const int SECOND_DOMAIN_ID = 2;

    /**
     * @inject
     */
    private OrmAdapterFactory $ormAdapterFactory;

    /**
     * The adapter resolves the locale of the translated fields from the current request, the same way it
     * does when a listing is rendered.
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequest();
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
        $adapter = $this->ormAdapterFactory->create(NewsletterSubscriber::class);
        $domainControlScope = new DomainControlScope(
            DomainControlType::FILTER,
            [self::FIRST_DOMAIN_ID, self::SECOND_DOMAIN_ID],
            $selectedDomainId,
            'crud_test',
            'domainId',
        );

        return $this->getDomainIdsOfRows($adapter->getDatasource(
            new DatasourceRequest('id', [new FieldDescriptor('domainId')], $domainControlScope->createCondition()),
        ));
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
