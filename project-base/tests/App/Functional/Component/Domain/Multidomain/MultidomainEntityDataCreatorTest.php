<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Domain\Multidomain;

use Shopsys\FrameworkBundle\Component\Doctrine\SqlQuoter;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassFinderFacade;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityDataCreator;
use Tests\App\Test\TransactionFunctionalTestCase;

class MultidomainEntityDataCreatorTest extends TransactionFunctionalTestCase
{
    public function testCopyAllMultidomainDataForNewDomainCopiesTestRow()
    {
        $this->em->getConnection()->executeQuery('
            CREATE TABLE _test_table (
                domain_id int NOT NULL,
                title text NOT NULL,
                description text
            )
        ');

        $this->em->getConnection()->executeQuery("
            INSERT INTO _test_table (domain_id, title, description)
                VALUES (1, 'asdf', 'qwer')
        ");

        $multidomainEntityClassFinderFacadeMock = $this->getMockBuilder(MultidomainEntityClassFinderFacade::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllNotNullableColumnNamesIndexedByTableName'])
            ->getMock();

        $multidomainEntityClassFinderFacadeMock
            ->method('getAllNotNullableColumnNamesIndexedByTableName')
            ->willReturn([
                '_test_table' => ['title'],
            ]);

        $sqlQuoter = new SqlQuoter($this->em);

        $multidomainEntityDataCreator = new MultidomainEntityDataCreator($multidomainEntityClassFinderFacadeMock, $this->em, $sqlQuoter);

        $multidomainEntityDataCreator->copyAllMultidomainDataForNewDomain(1, 2);

        $results = $this->em->getConnection()->fetchAll('
            SELECT domain_id, title, description
            FROM _test_table
            ORDER BY domain_id
        ');

        $expectedResults = [
            [
                'domain_id' => 1,
                'title' => 'asdf',
                'description' => 'qwer',
            ],
            [
                'domain_id' => 2,
                'title' => 'asdf',
                'description' => null,
            ],
        ];

        $this->assertSame($expectedResults, $results);
    }

    public function testCopyAllMultidomainDataForNewDomainWithDomainIdDoesNotThrowDriverException()
    {
        $this->em->getConnection()->executeQuery('
            CREATE TABLE _test_table (
                domain_id int NOT NULL,
                title text NOT NULL
            )
        ');

        $this->em->getConnection()->executeQuery("
            INSERT INTO _test_table (domain_id, title)
                VALUES (1, 'asdf')
        ");

        $multidomainEntityClassFinderFacadeMock = $this->getMockBuilder(MultidomainEntityClassFinderFacade::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllNotNullableColumnNamesIndexedByTableName'])
            ->getMock();

        $multidomainEntityClassFinderFacadeMock
            ->method('getAllNotNullableColumnNamesIndexedByTableName')
            ->willReturn([
                '_test_table' => ['domain_id', 'title'],
            ]);

        $sqlQuoter = new SqlQuoter($this->em);

        $multidomainEntityDataCreator = new MultidomainEntityDataCreator($multidomainEntityClassFinderFacadeMock, $this->em, $sqlQuoter);

        try {
            $multidomainEntityDataCreator->copyAllMultidomainDataForNewDomain(1, 2);
        } catch (\Doctrine\DBAL\Exception\DriverException $ex) {
            $this->fail('Exception not expected');
        }
    }
}
