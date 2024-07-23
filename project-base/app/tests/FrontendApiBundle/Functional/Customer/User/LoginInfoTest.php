<?php

declare(strict_types=1);

namespace FrontendApiBundle\Functional\Customer\User;

use App\Model\Customer\User\CustomerUserFacade;
use DateTime;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class LoginInfoTest extends GraphQlWithLoginTestCase
{
    private const string EXPECTED_LOGIN_TYPE = LoginTypeEnum::FACEBOOK;
    private const string EXPECTED_EXTERNAL_ID = '1234567890';

    /**
     * @inject
     */
    private CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory;

    /**
     * @inject
     */
    private CustomerUserLoginTypeFacade $customerUserLoginTypeFacade;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    public function testCustomerUserLoginInfo(): void
    {
        $this->createFacebookLogin();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CurrentCustomerUserQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'currentCustomerUser');

        $expected = [
            'loginType' => self::EXPECTED_LOGIN_TYPE,
            'externalId' => self::EXPECTED_EXTERNAL_ID,
        ];

        $this->assertEquals($expected, $data['loginInfo']);
    }

    private function createFacebookLogin(): void
    {
        $customerUserLoginTypeFacebookData = $this->customerUserLoginTypeDataFactory->create(
            $this->customerUserFacade->findCustomerUserByEmailAndDomain(self::DEFAULT_USER_EMAIL, $this->domain->getId()),
            self::EXPECTED_LOGIN_TYPE,
            self::EXPECTED_EXTERNAL_ID,
        );
        $customerUserLoginTypeFacebookData->lastLoggedInAt = new DateTime('+1 second');
        $this->customerUserLoginTypeFacade->updateCustomerUserLoginTypes($customerUserLoginTypeFacebookData);
    }
}
