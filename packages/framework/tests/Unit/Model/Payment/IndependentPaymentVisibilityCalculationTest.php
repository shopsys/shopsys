<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
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

    private Domain|Stub $domainStub;

    private CustomerUserRoleResolver|Stub $customerUserRoleResolverStub;

    private IndependentPaymentVisibilityCalculation $paymentVisibilityCalculation;

    #[Override]
    protected function setUp(): void
    {
        $this->domainStub = $this->createStub(Domain::class);
        $this->domainStub->method('getDomainConfigById')
            ->willReturn(
                DomainConfigHelper::getDomainConfig(),
            );

        $this->customerUserRoleResolverStub = $this->createStub(CustomerUserRoleResolver::class);
        $this->paymentVisibilityCalculation = new IndependentPaymentVisibilityCalculation(
            $this->domainStub,
            $this->customerUserRoleResolverStub,
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
        $paymentStub = $this->createStub(Payment::class);
        $paymentStub->method('isOnlinePayment')->willReturn($isOnlinePayment);
        $paymentStub->method('isHidden')->willReturn($isHidden);
        $paymentStub->method('isDeleted')->willReturn($isDeleted);
        $paymentStub->method('isHiddenByGoPayByDomainId')->willReturn($isHiddenByGoPay);
        $paymentStub->method('getName')->willReturn($name);
        $paymentStub->method('isEnabled')->willReturn($isEnabled);

        $this->customerUserRoleResolverStub->method('canCurrentCustomerUserSeePrices')->willReturn($canSeePrices);

        $this->assertEquals($expectedResult, $this->paymentVisibilityCalculation->isIndependentlyVisible($paymentStub, 1));
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
