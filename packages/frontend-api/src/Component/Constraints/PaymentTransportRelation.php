<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class PaymentTransportRelation extends Constraint
{
    public const INVALID_COMBINATION_ERROR = '46ccd6d3-61e7-4a34-a42a-b13b92291e28';

    public string $invalidCombinationMessage = 'Please choose a valid combination of transport and payment';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::INVALID_COMBINATION_ERROR => 'INVALID_COMBINATION_ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
