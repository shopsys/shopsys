<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class FriendlyUrlDataFixture extends AbstractReferenceFixture
{
    public function __construct(
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_product_detail',
                9999,
                'Unused friendly URL',
                $domainId,
            );
        }
    }
}
