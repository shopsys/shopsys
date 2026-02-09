<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class IndependentPaymentVisibilityCalculationTest extends TestCase
{
    use SetTranslatorTrait;

    private Domain|MockObject $domainMock;

    private CustomerUserRoleResolver|MockObject $customerUserRoleResolverMock;

    private IndependentPaymentVisibilityCalculation $paymentVisibilityCalculation;

    #[Override]
    protected function setUp(): void
    {
        $this->domainMock = $this->createMock(Domain::class);
        $this->domainMock->method('getDomainConfigById')
            ->willReturn(
                DomainConfigHelper::getDomainConfig(),
            );

        $this->customerUserRoleResolverMock = $this->createMock(CustomerUserRoleResolver::class);
        $this->paymentVisibilityCalculation = new IndependentPaymentVisibilityCalculation(
            $this->domainMock,
            $this->customerUserRoleResolverMock,
        );
    }

    #[DataProvider('paymentVisibilityProvider')]
    public function testIsIndependentlyVisible(
        bool $canSeePrices,
        bool $isOnlinePayment,
        bool $isHidden,
        bool $isDeleted,
        bool $isHiddenByGoPay,
        string $name,
        bool $isEnabled,
        bool $expectedResult,
    ): void {
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('isOnlinePayment')->willReturn($isOnlinePayment);
        $paymentMock->method('isHidden')->willReturn($isHidden);
        $paymentMock->method('isDeleted')->willReturn($isDeleted);
        $paymentMock->method('isHiddenByGoPayByDomainId')->willReturn($isHiddenByGoPay);
        $paymentMock->method('getName')->willReturn($name);
        $paymentMock->method('isEnabled')->willReturn($isEnabled);

        $this->customerUserRoleResolverMock->method('canCurrentCustomerUserSeePrices')->willReturn($canSeePrices);

        $this->assertEquals($expectedResult, $this->paymentVisibilityCalculation->isIndependentlyVisible($paymentMock, 1));
    }

    public static function paymentVisibilityProvider(): array
    {
        return [
            'Customer can see prices' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => true,
            ],
            'Payment name is empty' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => '',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is hidden' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => true,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is deleted' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => true,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is hidden by GoPay' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => true,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is enabled' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => true,
            ],
            'Payment is not enabled' => [
                'canSeePrices' => true,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => false,
                'expectedResult' => false,
            ],
            'Customer cannot see prices and payment is an online payment' => [
                'canSeePrices' => false,
                'isOnlinePayment' => true,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Customer cannot see prices and payment is not an online payment' => [
                'canSeePrices' => false,
                'isOnlinePayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => true,
            ],
        ];
    }
}
