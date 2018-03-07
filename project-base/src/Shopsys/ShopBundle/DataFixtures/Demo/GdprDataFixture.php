<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\Gdpr\GdprFacade;
use Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequestData;

class GdprDataFixture extends AbstractReferenceFixture
{
    const PERSONAL_DATA_ACCESS_REQUEST = 'personal_data_access_request';

    public function load(ObjectManager $manager)
    {
        $gdprFacade = $this->get(GdprFacade::class);
        /* @var $gdprFacade \Shopsys\ShopBundle\Model\Gdpr\GdprFacade */
        $personalDataAccessRequestData = new PersonalDataAccessRequestData();
        $personalDataAccessRequestData->domainId = Domain::FIRST_DOMAIN_ID;
        $personalDataAccessRequestData->email = 'no-reply@netdevelo.cz';
        $personalDataAccessRequestData->createAt = new \DateTime();
        $personalDataAccessRequestData->hash = 'UrSqiLmCK0cdGfBuwRza';
        $personalDataAccessRequest = $gdprFacade->createPersonalDataAccessRequest($personalDataAccessRequestData, Domain::FIRST_DOMAIN_ID);
        $this->addReference(self::PERSONAL_DATA_ACCESS_REQUEST, $personalDataAccessRequest);
    }
}
