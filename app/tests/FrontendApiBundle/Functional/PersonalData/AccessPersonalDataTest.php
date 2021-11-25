<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\PersonalData;

use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AccessPersonalDataTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest
     */
    private PersonalDataAccessRequest $personalDataAccessRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $personalDataAccessRequestDataFactory = $this->getContainer()->get(PersonalDataAccessRequestDataFactory::class);
        $personalDataAccessRequestFacade = $this->getContainer()->get(PersonalDataAccessRequestFacade::class);

        $personalDataAccessRequest = $personalDataAccessRequestDataFactory->createForDisplay();
        $personalDataAccessRequest->email = 'no-reply@shopsys.com';

        $this->personalDataAccessRequest = $personalDataAccessRequestFacade->createPersonalDataAccessRequest(
            $personalDataAccessRequest,
            $this->domain->getId()
        );
    }

    public function testAccessPersonalData(): void
    {
        $query = '
            query accessPersonalData {
                accessPersonalData(hash: "' . $this->personalDataAccessRequest->getHash() . '") {
                    orders {
                        uuid
                    }
                    customerUser {
                        firstName
                        lastName
                        email
                    }
                    newsletterSubscriber {
                        email
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $accessPersonalDataData = $response['data']['accessPersonalData'];

        $this->assertCount(16, $accessPersonalDataData['orders']);

        $expectedCustomerUser = [
            'firstName' => 'Jaromír',
            'lastName' => 'Jágr',
            'email' => 'no-reply@shopsys.com',
        ];

        $this->assertSame($accessPersonalDataData['customerUser'], $expectedCustomerUser);

        $expectedNewsletterSubscriber = [
            'email' => 'no-reply@shopsys.com',
        ];

        $this->assertSame($accessPersonalDataData['newsletterSubscriber'], $expectedNewsletterSubscriber);
    }
}
