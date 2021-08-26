<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Doctrine\ORM\EntityManagerInterface;

abstract class TransactionFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     * @inject
     */
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em->beginTransaction();
        $this->em->getConnection()->setAutoCommit(false);
    }

    protected function tearDown(): void
    {
        $this->em->rollback();

        parent::tearDown();
    }
}
