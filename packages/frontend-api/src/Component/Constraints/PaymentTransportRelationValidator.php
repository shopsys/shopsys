<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PaymentTransportRelationValidator extends ConstraintValidator
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
        protected readonly TransportFacade $transportFacade,
    ) {
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
