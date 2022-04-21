<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use App\Model\Payment\PaymentFacade;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentInCartValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private PaymentFacade $paymentFacade;

    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     */
    public function __construct(PaymentFacade $paymentFacade, Domain $domain)
    {
        $this->paymentFacade = $paymentFacade;
        $this->domain = $domain;
    }

    /**
     * @param mixed $value
     * @param \App\FrontendApi\Model\Component\Constraints\PaymentInCart $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PaymentInCart) {
            throw new UnexpectedTypeException($constraint, PaymentInCart::class);
        }
        $paymentUuid = $value->paymentUuid;
        if ($paymentUuid === null) {
            return;
        }
        try {
            $this->paymentFacade->getEnabledOnDomainByUuid($paymentUuid, $this->domain->getId());
        } catch (PaymentNotFoundException $exception) {
            $this->context->buildViolation($constraint->unavailablePaymentMessage)
                ->setCode($constraint::UNAVAILABLE_PAYMENT_ERROR)
                ->atPath('paymentUuid')
                ->addViolation();
        }
    }
}
