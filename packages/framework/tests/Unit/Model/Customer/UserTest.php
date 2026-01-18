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

    /**
     * @param mixed $resetPasswordHash
     * @param mixed $resetPasswordHashValidThrough
     * @param mixed $sentHash
     * @param mixed $isExpectedValid
     */
    #[DataProvider('isResetPasswordHashValidProvider')]
    public function testIsResetPasswordHashValid(
        $resetPasswordHash,
        $resetPasswordHashValidThrough,
        $sentHash,
        $isExpectedValid,
    ): void {
        $customerUser = TestCustomerProvider::getTestCustomerUser();

        $this->setValueOfProtectedProperty($customerUser, 'resetPasswordHash', $resetPasswordHash);
        $this->setValueOfProtectedProperty($customerUser, 'resetPasswordHashValidThrough', $resetPasswordHashValidThrough);

        $isResetPasswordHashValid = $customerUser->isResetPasswordHashValid($sentHash);

        $this->assertSame($isExpectedValid, $isResetPasswordHashValid);
    }
}
