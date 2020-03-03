<?php

declare(strict_types=1);

namespace Tests\App\Test;

abstract class TransactionFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getEntityManager()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->getEntityManager()->rollback();

        parent::tearDown();
    }
}
