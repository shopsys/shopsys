<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Transport;

use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class TransportDomainTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     * @inject
     */
    private TransportDataFactoryInterface $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface
     * @inject
     */
    private TransportFactoryInterface $transportFactory;

    public function testCreateTransportEnabledOnDomain(): void
    {
        $transportData = $this->transportDataFactory->create();

        $transportData->enabled[self::FIRST_DOMAIN_ID] = true;

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->transportFactory->create($transportData);

        $refreshedTransport = $this->getRefreshedTransportFromDatabase($transport);

        $this->assertTrue($refreshedTransport->isEnabled(self::FIRST_DOMAIN_ID));
    }

    public function testCreateTransportDisabledOnDomain(): void
    {
        $transportData = $this->transportDataFactory->create();

        $transportData->enabled[self::FIRST_DOMAIN_ID] = false;

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->transportFactory->create($transportData);

        $refreshedTransport = $this->getRefreshedTransportFromDatabase($transport);

        $this->assertFalse($refreshedTransport->isEnabled(self::FIRST_DOMAIN_ID));
    }

    public function testCreateTransportWithDifferentVisibilityOnDomains(): void
    {
        if (count($this->domain->getAllIds()) === 1) {
            $this->markTestSkipped('Test is skipped for single domain');
        }

        $transportData = $this->transportDataFactory->create();

        $transportData->enabled[self::FIRST_DOMAIN_ID] = true;
        $transportData->enabled[self::SECOND_DOMAIN_ID] = false;

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->transportFactory->create($transportData);

        $refreshedTransport = $this->getRefreshedTransportFromDatabase($transport);

        $this->assertTrue($refreshedTransport->isEnabled(self::FIRST_DOMAIN_ID));
        $this->assertFalse($refreshedTransport->isEnabled(self::SECOND_DOMAIN_ID));
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Transport\Transport
     */
    private function getRefreshedTransportFromDatabase(Transport $transport): Transport
    {
        $this->em->persist($transport);
        $this->em->flush();

        $transportId = $transport->getId();

        $this->em->clear();

        return $this->em->getRepository(Transport::class)->find($transportId);
    }
}
