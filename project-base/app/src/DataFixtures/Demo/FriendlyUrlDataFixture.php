<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class FriendlyUrlDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(private FriendlyUrlFacade $friendlyUrlFacade)
    {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->friendlyUrlFacade->createFriendlyUrlForDomain(
            'front_product_detail',
            9999,
            'Unused friendly URL',
            Domain::FIRST_DOMAIN_ID,
        );
    }
}
