<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\FrontendApi\Model\Component\Constraints\ExistingEmail;
use App\FrontendApi\Model\Component\Constraints\ResetPasswordHash;
use App\Model\Customer\User\CustomerUser;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RecoverPasswordTest extends GraphQlTestCase
{
    public function testRequestPasswordRecovery(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);
        $query = '
            mutation {
                RecoverPassword(input: {
                    email: "' . $customerUser->getEmail() . '"
                    hash: "' . $customerUser->getResetPasswordHash() . '"
                    newPassword: "password123"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                }
            }';

        $response = $this->getResponseContentForQuery($query);

        $recoverPasswordData = $this->getResponseDataForGraphQlType($response, 'RecoverPassword');

        $this->assertArrayHasKey('tokens', $recoverPasswordData);
        $this->assertIsString($recoverPasswordData['tokens']['accessToken']);

        $this->assertArrayHasKey('tokens', $recoverPasswordData);
        $this->assertIsString($recoverPasswordData['tokens']['refreshToken']);
    }

    public function testRequestPasswordRecoveryWithInvalidHash(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);
        $query = '
            mutation {
                RecoverPassword(input: {
                    email: "' . $customerUser->getEmail() . '"
                    hash: "Lorem ipsum dolor sit amet, consectetur tincidunt."
                    newPassword: "password123"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                }
            }';

        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertCount(1, $validationErrors);
        $this->assertSame(ResetPasswordHash::INVALID_HASH_ERROR, $validationErrors['input.hash'][0]['code']);
    }

    public function testRequestPasswordRecoveryWithInvalidEmail(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);
        $query = '
            mutation {
                RecoverPassword(input: {
                    email: "no-reply-not-existing@shopsys.com"
                    hash: "' . $customerUser->getResetPasswordHash() . '"
                    newPassword: "password123"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                }
            }';

        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertCount(1, $validationErrors);
        $this->assertSame(ExistingEmail::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR, $validationErrors['input.email'][0]['code']);
    }
}
