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

    /**
     * @return int
     */
    protected function getNextPaymentExternalId(): int
    {
        return $this->em->createQuery('select max(p.externalId)+1 from App\Model\Payment\Payment p')->getSingleScalarResult();
    }

    /**
     * @return int
     */
    protected function getNextTransportExternalId(): int
    {
        return $this->em->createQuery('select max(t.externalId)+1 from App\Model\Transport\Transport t')->getSingleScalarResult();
    }
}
