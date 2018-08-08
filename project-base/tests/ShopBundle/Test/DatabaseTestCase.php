<?php

namespace Tests\ShopBundle\Test;

abstract class DatabaseTestCase extends FunctionalTestCase
{
    protected function getEntityManager(): \Doctrine\ORM\EntityManagerInterface
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
