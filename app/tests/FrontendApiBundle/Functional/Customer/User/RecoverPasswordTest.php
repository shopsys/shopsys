<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RecoverPasswordTest extends GraphQlTestCase
{
    public function testRequestPasswordRecovery(): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH);
        $query = '
            mutation {
                RecoverPassword(input: {
                    email: "' . $customerUser->getEmail() . '"
                    hash: "' . $customerUser->getResetPasswordHash() . '"
                    newPassword: "password123"
                }) {
                    accessToken
                    refreshToken
                }
            }';

        $response = $this->getResponseContentForQuery($query);

        $recoverPasswordData = $this->getResponseDataForGraphQlType($response, 'RecoverPassword');

        $this->assertArrayHasKey('accessToken', $recoverPasswordData);
        $this->assertIsString($recoverPasswordData['accessToken']);

        $this->assertArrayHasKey('refreshToken', $recoverPasswordData);
        $this->assertIsString($recoverPasswordData['refreshToken']);
    }
}
