<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Clock\DatePoint;
use Tests\FrameworkBundle\Unit\TestCase;

class UserTest extends TestCase
{
    public function testGetFullNameReturnsLastnameAndFirstnameForUser(): void
    {
        $customerUserData = TestCustomerProvider::getTestCustomerUserData(false);
        $customerUser = new CustomerUser($customerUserData);

        $this->assertSame('Lastname Firstname', $customerUser->getFullName());
    }

    public function testGetFullNameReturnsCompanyNameForCompanyUser(): void
    {
        $customerUser = TestCustomerProvider::getTestCustomerUser();

        $this->assertSame('companyName', $customerUser->getFullName());
    }

    public function testEmailIsLowercasedWhenSet(): void
    {
        $customerUserData = TestCustomerProvider::getTestCustomerUserData();
        $customerUserData->email = 'No-Reply@Shopsys.COM';
        $customerUser = new CustomerUser($customerUserData);

        $this->assertSame('no-reply@shopsys.com', $customerUser->getEmail());

        $customerUser->setEmail('Another.User@Example.ORG');

        $this->assertSame('another.user@example.org', $customerUser->getEmail());
    }

    public function testEmailIsLowercasedWhenUnserialized(): void
    {
        $customerUser = TestCustomerProvider::getTestCustomerUser();
        $customerUser->__unserialize([
            'id' => 1,
            'email' => 'Mixed.Case@Example.COM',
            'password' => 'hash',
            'timestamp' => 0,
            'domainId' => 1,
        ]);

        $this->assertSame('mixed.case@example.com', $customerUser->getEmail());
    }

    public static function isResetPasswordHashValidProvider(): array
    {
        return [
            [
                'resetPasswordHash' => 'validHash',
                'resetPasswordHashValidThrough' => (new DatePoint())->modify('+1 hour'),
                'sentHash' => 'validHash',
                'isExpectedValid' => true,
            ],
            [
                'resetPasswordHash' => null,
                'resetPasswordHashValidThrough' => (new DatePoint())->modify('+1 hour'),
                'sentHash' => 'hash',
                'isExpectedValid' => false,
            ],
            [
                'resetPasswordHash' => 'validHash',
                'resetPasswordHashValidThrough' => (new DatePoint())->modify('+1 hour'),
                'sentHash' => 'invalidHash',
                'isExpectedValid' => false,
            ],
            [
                'resetPasswordHash' => 'validHash',
                'resetPasswordHashValidThrough' => null,
                'sentHash' => 'validHash',
                'isExpectedValid' => false,
            ],
            [
                'resetPasswordHash' => 'validHash',
                'resetPasswordHashValidThrough' => (new DatePoint())->modify('-1 hour'),
                'sentHash' => 'validHash',
                'isExpectedValid' => false,
            ],
        ];
    }

    #[DataProvider('isResetPasswordHashValidProvider')]
    public function testIsResetPasswordHashValid(
        mixed $resetPasswordHash,
        mixed $resetPasswordHashValidThrough,
        mixed $sentHash,
        mixed $isExpectedValid,
    ): void {
        $customerUser = TestCustomerProvider::getTestCustomerUser();

        $this->setValueOfProtectedProperty($customerUser, 'resetPasswordHash', $resetPasswordHash);
        $this->setValueOfProtectedProperty($customerUser, 'resetPasswordHashValidThrough', $resetPasswordHashValidThrough);

        $isResetPasswordHashValid = $customerUser->isResetPasswordHashValid($sentHash);

        $this->assertSame($isExpectedValid, $isResetPasswordHashValid);
    }
}
