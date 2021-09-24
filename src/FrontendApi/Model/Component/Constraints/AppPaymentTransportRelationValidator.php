<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Shopsys\FrontendApiBundle\Component\Constraints\PaymentTransportRelationValidator;
use Symfony\Component\Validator\Constraint;

/**
 * The class need the prefix because of the conflicting name in GraphQL generated classes
 *
 * @see https://github.com/overblog/GraphQLBundle/issues/863
 */
class AppPaymentTransportRelationValidator extends PaymentTransportRelationValidator
{
    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value->payment === null || $value->transport === null) {
            return;
        }

        parent::validate($value, $constraint);
    }
}
