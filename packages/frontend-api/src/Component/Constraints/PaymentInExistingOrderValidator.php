<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Payment\Exception\PaymentNotFoundUserError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInExistingOrderValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly GoPayBankSwiftFacade $goPayBankSwiftFacade,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentInExistingOrder) {
            throw new UnexpectedTypeException($constraint, PaymentInExistingOrder::class);
        }

        try {
            $order = $this->orderFacade->getByUuid($value->orderUuid);
            $payment = $this->paymentFacade->getByUuid($value->paymentUuid);
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError('Order with UUID \'' . $value->orderUuid . '\' not found.');
        } catch (PaymentNotFoundException) {
            throw new PaymentNotFoundUserError('Payment with UUID \'' . $value->paymentUuid . '\' not found.');
        }
        $paymentGoPayBankSwift = $value->paymentGoPayBankSwift;

        $this->validatePaymentCanBeChanged($order, $constraint);
        $this->validatePaymentIsAvailable($order, $payment, $constraint);
        $this->validateSwift($order, $payment, $paymentGoPayBankSwift, $constraint);
    }

    protected function validateSwift(
        Order $order,
        Payment $payment,
        ?string $paymentGoPayBankSwift,
        PaymentInExistingOrder $constraint,
    ): void {
        $goPayPaymentMethod = $payment->getGoPayPaymentMethodByDomainId($order->getDomainId());

        if ($paymentGoPayBankSwift === null || $goPayPaymentMethod === null) {
            return;
        }

        $currency = $this->currencyFacade->getByCode($order->getCurrencyCode());
        $goPayBankSwift = $this->goPayBankSwiftFacade->findBySwiftAndPaymentMethodAndCurrency(
            $paymentGoPayBankSwift,
            $goPayPaymentMethod,
            $currency,
        );

        if ($goPayBankSwift !== null) {
            return;
        }

        $this->context
            ->buildViolation($constraint->invalidPaymentSwiftMessage, [
                'paymentUuid' => $payment->getUuid(),
                'swift' => $paymentGoPayBankSwift,
            ])
            ->setCode(PaymentInExistingOrder::INVALID_PAYMENT_SWIFT_ERROR)
            ->addViolation();
    }

    protected function validatePaymentCanBeChanged(Order $order, PaymentInExistingOrder $constraint): void
    {
        if ($order->isPaid() === false && $order->getPayment()->isGoPay()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->unchangeablePaymentMessage)
            ->setCode(PaymentInExistingOrder::UNCHANGEABLE_PAYMENT_ERROR)
            ->addViolation();
    }

    protected function validatePaymentIsAvailable(
        Order $order,
        Payment $payment,
        PaymentInExistingOrder $constraint,
    ): void {
        $availablePayments = $this->paymentFacade->getVisibleForOrder($order);

        if (in_array($payment, $availablePayments, true)) {
            return;
        }

        $this->context
            ->buildViolation($constraint->unavailablePaymentMessage, [
                'paymentUuid' => $payment->getUuid(),
                'orderUuid' => $order->getUuid(),
            ])
            ->setCode(PaymentInExistingOrder::UNAVAILABLE_PAYMENT_ERROR)
            ->addViolation();
    }
}
