<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PaymentTransportRelation extends Constraint
{
    public const INVALID_COMBINATION_ERROR = '46ccd6d3-61e7-4a34-a42a-b13b92291e28';

    public $invalidCombinationMessage = 'Please choose a valid combination of transport and payment';

    protected static $errorNames = [
        self::INVALID_COMBINATION_ERROR => 'INVALID_COMBINATION_ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
