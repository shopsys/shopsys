<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Transport;

use Shopsys\ShopBundle\Model\Transport\Transport;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class TransportDomainTest extends TransactionFunctionalTestCase
{
    protected const FIRST_DOMAIN_ID = 1;
    protected const SECOND_DOMAIN_ID = 2;

    /**
     * @var \Shopsys\ShopBundle\Model\Transport\TransportDataFactory
     * @inject
     */
    private $transportDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFactoryInterface
     * @inject
     */
    private $transportFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    public function setUp()
    {
        parent::setUp();
        $this->em = $this->getEntityManager();
    }

    public function testCreateTransportEnabledOnDomain()
    {
        $transportData = $this->transportDataFactory->create();

        $transportData->enabled[self::FIRST_DOMAIN_ID] = true;

        $transport = $this->transportFactory->create($transportData);

        $refreshedTransport = $this->getRefreshedTransportFromDatabase($transport);

        $this->assertTrue($refreshedTransport->isEnabled(self::FIRST_DOMAIN_ID));
    }

    public function testCreateTransportDisabledOnDomain()
    {
        $transportData = $this->transportDataFactory->create();

        $transportData->enabled[self::FIRST_DOMAIN_ID] = false;

        $transport = $this->transportFactory->create($transportData);

        $refreshedTransport = $this->getRefreshedTransportFromDatabase($transport);

        $this->assertFalse($refreshedTransport->isEnabled(self::FIRST_DOMAIN_ID));
    }

    public function testCreateTransportWithDifferentVisibilityOnDomains()
    {
        $transportData = $this->transportDataFactory->create();

        $transportData->enabled[self::FIRST_DOMAIN_ID] = true;
        $transportData->enabled[self::SECOND_DOMAIN_ID] = false;

        $transport = $this->transportFactory->create($transportData);

        $refreshedTransport = $this->getRefreshedTransportFromDatabase($transport);

        $this->assertTrue($refreshedTransport->isEnabled(self::FIRST_DOMAIN_ID));
        $this->assertFalse($refreshedTransport->isEnabled(self::SECOND_DOMAIN_ID));
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Transport\Transport $transport
     * @return \Shopsys\ShopBundle\Model\Transport\Transport
     */
    private function getRefreshedTransportFromDatabase(Transport $transport)
    {
        $this->em->persist($transport);
        $this->em->flush();

        $transportId = $transport->getId();

        $this->em->clear();

        return $this->em->getRepository(Transport::class)->find($transportId);
    }
}
