<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Administrator\Administrator;
use App\Model\Customer\User\CustomerUser;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenFacade;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenFactory;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use Throwable;

class LoginViaExchangeTokenTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private TokenFacade $tokenFacade;

    /**
     * @inject
     */
    private LoginAsUserExchangeTokenFacade $loginAsUserExchangeTokenFacade;

    /**
     * @inject
     */
    private LoginAsUserExchangeTokenFactory $loginAsUserExchangeTokenFactory;

    /**
     * @inject
     */
    private EntityManagerInterface $entityManager;

    /**
     * @inject
     */
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function testLoginViaExchangeTokenMutationWithValidToken(): void
    {
        $graphQlType = 'LoginViaExchangeToken';

        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $unencryptedToken = $this->loginAsUserExchangeTokenFacade->createAndGetUnencryptedToken($customerUser, $administrator);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginViaExchangeTokenMutation.graphql',
            ['exchangeToken' => $unencryptedToken],
        );
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertIsString($responseData['accessToken']);

        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertIsString($responseData['refreshToken']);

        // Verify tokens are valid
        try {
            $token = $this->tokenFacade->getTokenByString($responseData['accessToken']);
            $claims = $token->claims();
            $this->assertTrue($claims->has(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID));
            $this->assertSame($administrator->getUuid(), $claims->get(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID));
            $this->assertTrue($claims->has(FrontendApiUser::CLAIM_EMAIL));
            $this->assertSame($customerUser->getEmail(), $claims->get(FrontendApiUser::CLAIM_EMAIL));
        } catch (Throwable) {
            $this->fail('Access token is not valid');
        }

        try {
            $this->tokenFacade->getTokenByString($responseData['refreshToken']);
        } catch (Throwable) {
            $this->fail('Refresh token is not valid');
        }
    }

    public function testLoginViaExchangeTokenMutationWithInvalidToken(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginViaExchangeTokenMutation.graphql',
            ['exchangeToken' => 'invalid-token'],
        );

        $this->assertInvalidTokenError($response);
    }

    public function testLoginViaExchangeTokenMutationWithExpiredToken(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $expiredToken = $this->createExpiredExchangeToken($customerUser, $administrator);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginViaExchangeTokenMutation.graphql',
            ['exchangeToken' => $expiredToken],
        );

        $this->assertInvalidTokenError($response);
    }

    public function testLoginViaExchangeTokenMutationWithUsedToken(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);

        $unencryptedToken = $this->loginAsUserExchangeTokenFacade->createAndGetUnencryptedToken($customerUser, $administrator);

        // Use token once
        $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginViaExchangeTokenMutation.graphql',
            ['exchangeToken' => $unencryptedToken],
        );

        // Verify the exchange token is no longer valid (one-time use)
        $this->assertNull($this->loginAsUserExchangeTokenFacade->findValidByToken($unencryptedToken), 'Exchange token should be deleted after use');

        // Try to use the same token again - should fail
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginViaExchangeTokenMutation.graphql',
            ['exchangeToken' => $unencryptedToken],
        );

        $this->assertInvalidTokenError($response);
    }

    /**
     * @param array $response
     */
    private function assertInvalidTokenError(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);

        $error = $this->getErrorsFromResponse($response)[0];

        $this->assertSame('Invalid or expired exchange token.', $error['message']);
        $this->assertSame('invalid-credentials', $error['extensions']['userCode']);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Administrator\Administrator $administrator
     * @return string
     */
    private function createExpiredExchangeToken(CustomerUser $customerUser, Administrator $administrator): string
    {
        $expiredDate = new DateTime('-1 minute');
        $unencryptedToken = 'xxx';
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(Administrator::class);
        $hashedToken = $passwordHasher->hash($unencryptedToken);
        $expiredExchangeToken = $this->loginAsUserExchangeTokenFactory->create($hashedToken, $customerUser, $administrator, $expiredDate);

        $this->entityManager->persist($expiredExchangeToken);
        $this->entityManager->flush();

        return $unencryptedToken;
    }
}
