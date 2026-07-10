<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroupFacade;

class TransportGroupDataFixture extends AbstractReferenceFixture
{
    public const string TRANSPORT_GROUP_DELIVERY_TO_ADDRESS = 'transport_group_delivery_to_address';
    public const string TRANSPORT_GROUP_PICKUP_POINT = 'transport_group_pickup_point';

    public function __construct(
        private readonly TransportGroupFacade $transportGroupFacade,
    ) {
    }

    /**
     * Default transport groups are created by a database migration ordered by position.
     *
     * @see \Shopsys\FrameworkBundle\Migrations\Version20260617120000
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $transportGroups = $this->transportGroupFacade->getAll();

        $this->addReference(self::TRANSPORT_GROUP_DELIVERY_TO_ADDRESS, $transportGroups[0]);
        $this->addReference(self::TRANSPORT_GROUP_PICKUP_POINT, $transportGroups[1]);
    }
}
