<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentTransportRelationValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    protected $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    protected $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    protected $transportFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        TransportFacade $transportFacade,
        Domain $domain,
        PaymentPriceCalculation $paymentPriceCalculation,
        CurrencyFacade $currencyFacade
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->domain = $domain;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->currencyFacade = $currencyFacade;
        $this->transportFacade = $transportFacade;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentTransportRelation) {
            throw new UnexpectedTypeException($constraint, PaymentTransportRelation::class);
        }

        try {
            $payment = $this->paymentFacade->getByUuid($value->payment['uuid']);
            $transport = $this->transportFacade->getByUuid($value->transport['uuid']);

            $relationExists = in_array($transport, $payment->getTransports(), true);

            if (!$relationExists) {
                $this->context->buildViolation($constraint->invalidCombinationMessage)
                    ->setCode(PaymentTransportRelation::INVALID_COMBINATION_ERROR)
                    ->addViolation();
            }
        } catch (PaymentNotFoundException | TransportNotFoundException $exception) {
            return;
        }
    }
}
