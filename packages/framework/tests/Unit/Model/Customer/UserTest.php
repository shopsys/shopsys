<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Tests\FrameworkBundle\Unit\TestCase;

class UserTest extends TestCase
{
    public function testGetFullNameReturnsLastnameAndFirstnameForUser()
    {
        $customerUserData = TestCustomerProvider::getTestCustomerUserData(false);
        $customerUser = new CustomerUser($customerUserData);

        $this->assertSame('Lastname Firstname', $customerUser->getFullName());
    }

    public function testGetFullNameReturnsCompanyNameForCompanyUser()
    {
        $customerUser = TestCustomerProvider::getTestCustomerUser();

        $this->assertSame('companyName', $customerUser->getFullName());
    }

    public static function isResetPasswordHashValidProvider()
    {
        return [
            [
                'resetPasswordHash' => 'validHash',
                'resetPasswordHashValidThrough' => new DateTime('+1 hour'),
                'sentHash' => 'validHash',
                'isExpectedValid' => true,
            ],
            [
                'resetPasswordHash' => null,
                'resetPasswordHashValidThrough' => new DateTime('+1 hour'),
                'sentHash' => 'hash',
                'isExpectedValid' => false,
            ],
            [
                'resetPasswordHash' => 'validHash',
                'resetPasswordHashValidThrough' => new DateTime('+1 hour'),
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
                'resetPasswordHashValidThrough' => new DateTime('-1 hour'),
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
    ) {
        $customerUser = TestCustomerProvider::getTestCustomerUser();

        $this->setValueOfProtectedProperty($customerUser, 'resetPasswordHash', $resetPasswordHash);
        $this->setValueOfProtectedProperty($customerUser, 'resetPasswordHashValidThrough', $resetPasswordHashValidThrough);

        $isResetPasswordHashValid = $customerUser->isResetPasswordHashValid($sentHash);

        $this->assertSame($isExpectedValid, $isResetPasswordHashValid);
    }
}
