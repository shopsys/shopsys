<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment;

use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Payment\IndependentPaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class IndependentPaymentVisibilityCalculationTest extends TestCase
{
    use SetTranslatorTrait;

    private Domain|MockObject $domainMock;

    private CustomerUserRoleResolver|MockObject $customerUserRoleResolverMock;

    private IndependentPaymentVisibilityCalculation $paymentVisibilityCalculation;

    protected function setUp(): void
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $this->domainMock = $this->createMock(Domain::class);
        $this->domainMock->method('getDomainConfigById')
            ->willReturn(
                new DomainConfig(Domain::FIRST_DOMAIN_ID, '', '', 'cs', $defaultTimeZone),
            );

        $this->customerUserRoleResolverMock = $this->createMock(CustomerUserRoleResolver::class);
        $this->paymentVisibilityCalculation = new IndependentPaymentVisibilityCalculation(
            $this->domainMock,
            $this->customerUserRoleResolverMock,
        );
    }

    /**
     * @param bool $canSeePrices
     * @param bool $isGatewayPayment
     * @param bool $isHidden
     * @param bool $isDeleted
     * @param bool $isHiddenByGoPay
     * @param string $name
     * @param bool $isEnabled
     * @param bool $expectedResult
     */
    #[DataProvider('paymentVisibilityProvider')]
    public function testIsIndependentlyVisible(
        bool $canSeePrices,
        bool $isGatewayPayment,
        bool $isHidden,
        bool $isDeleted,
        bool $isHiddenByGoPay,
        string $name,
        bool $isEnabled,
        bool $expectedResult,
    ) {
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('isGatewayPayment')->willReturn($isGatewayPayment);
        $paymentMock->method('isHidden')->willReturn($isHidden);
        $paymentMock->method('isDeleted')->willReturn($isDeleted);
        $paymentMock->method('isHiddenByGoPayByDomainId')->willReturn($isHiddenByGoPay);
        $paymentMock->method('getName')->willReturn($name);
        $paymentMock->method('isEnabled')->willReturn($isEnabled);

        $this->customerUserRoleResolverMock->method('canCurrentCustomerUserSeePrices')->willReturn($canSeePrices);
        $this->domainMock->method('getDomainConfigById')->willReturn((object)['locale' => 'en']);

        $this->assertEquals($expectedResult, $this->paymentVisibilityCalculation->isIndependentlyVisible($paymentMock, 1));
    }

    /**
     * @return array
     */
    public static function paymentVisibilityProvider(): array
    {
        return [
            'Customer can see prices' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => true,
            ],
            'Payment name is empty' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => '',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is hidden' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => true,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is deleted' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => false,
                'isDeleted' => true,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is hidden by GoPay' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => true,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Payment is enabled' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => true,
            ],
            'Payment is not enabled' => [
                'canSeePrices' => true,
                'isGatewayPayment' => false,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => false,
                'expectedResult' => false,
            ],
            'Customer cannot see prices and payment is a gateway payment' => [
                'canSeePrices' => false,
                'isGatewayPayment' => true,
                'isHidden' => false,
                'isDeleted' => false,
                'isHiddenByGoPay' => false,
                'name' => 'Payment Name',
                'isEnabled' => true,
                'expectedResult' => false,
            ],
            'Customer cannot see prices and payment is not a gateway payment' => [
                'canSeePrices' => false,
                'isGatewayPayment' => false,
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
