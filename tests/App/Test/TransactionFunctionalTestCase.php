<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

abstract class TransactionFunctionalTestCase extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     * @inject
     */
    protected $em;

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
