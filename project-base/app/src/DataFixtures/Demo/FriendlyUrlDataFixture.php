<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FriendlyUrlDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
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
