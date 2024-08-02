<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade;

class SalesRepresentativeDataFixture extends AbstractReferenceFixture
{
    private const string UUID_NAMESPACE = 'd660b914-5765-42ec-a64a-df9661e6af21';

    public const string SALES_REPRESENTATIVE_1 = 'sales_representative_1';
    public const string SALES_REPRESENTATIVE_2 = 'sales_representative_2';

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade $salesRepresentativeFacade
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeDataFactory $salesRepresentativeDataFactory
     */
    public function __construct(
        private readonly SalesRepresentativeFacade $salesRepresentativeFacade,
        private readonly SalesRepresentativeDataFactoryInterface $salesRepresentativeDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $salesRepresentativeData = $this->salesRepresentativeDataFactory->create();
        $salesRepresentativeData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'JD')->toString();
        $salesRepresentativeData->firstName = 'Jan';
        $salesRepresentativeData->lastName = 'Dvořák';
        $salesRepresentativeData->email = 'no-reply99@shopsys.com';
        $salesRepresentativeData->telephone = '585425321';
        $salesRepresentative = $this->salesRepresentativeFacade->create($salesRepresentativeData);
        $this->addReference(self::SALES_REPRESENTATIVE_1, $salesRepresentative);

        $salesRepresentativeData = $this->salesRepresentativeDataFactory->create();
        $salesRepresentativeData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'PS')->toString();
        $salesRepresentativeData->firstName = 'Petra';
        $salesRepresentativeData->lastName = 'Svobodová';
        $salesRepresentativeData->email = 'no-reply101@shopsys.com';
        $salesRepresentativeData->telephone = '424232535';
        $salesRepresentative = $this->salesRepresentativeFacade->create($salesRepresentativeData);
        $this->addReference(self::SALES_REPRESENTATIVE_2, $salesRepresentative);
    }
}
