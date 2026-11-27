<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class PickupPlaceOpeningHoursSize extends Constraint
{
    public const TOO_MANY_DAYS_ERROR = 'ebd8ccd1-cc5f-436d-b3ac-610ff7e81712';
    public const TOO_MANY_EXCEPTION_DAYS_ERROR = 'bd0fcbfd-6486-4337-9dad-2df58c0e25cf';
    public const TOO_MANY_OPENING_HOURS_RANGES_ERROR = '484d6fd3-fc4b-44e4-9dae-5a1f7526c7eb';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::TOO_MANY_DAYS_ERROR => 'TOO_MANY_DAYS_ERROR',
        self::TOO_MANY_EXCEPTION_DAYS_ERROR => 'TOO_MANY_EXCEPTION_DAYS_ERROR',
        self::TOO_MANY_OPENING_HOURS_RANGES_ERROR => 'TOO_MANY_OPENING_HOURS_RANGES_ERROR',
    ];

    /**
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $tooManyDaysMessage = 'Opening hours cannot contain more than {{ limit }} days',
        public string $tooManyExceptionDaysMessage = 'Opening hours cannot contain more than {{ limit }} exception days',
        public string $tooManyOpeningHoursRangesMessage = 'A day cannot contain more than {{ limit }} opening hours ranges',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
