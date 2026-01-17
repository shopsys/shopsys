<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class MoneyRange extends Constraint
{
    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public ?Money $min = null,
        public ?Money $max = null,
        public string $minMessage = 'The amount of money should be {{ limit }} or more.',
        public string $maxMessage = 'The amount of money should be {{ limit }} or less.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        if (is_array($options)) {
            DeprecationHelper::trigger(
                'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.',
                static::class,
            );
        }

        $this->validateMoneyOrNullOption('min', $min);
        $this->validateMoneyOrNullOption('max', $max);

        parent::__construct($options, $groups, $payload);

        if ($this->min === null && $this->max === null) {
            $message = sprintf('Either option "min" or "max" must be given for constraint "%s".', self::class);

            throw new MissingOptionsException($message, ['min', 'max']);
        }
    }

    protected function validateMoneyOrNullOption(string $optionName, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (!($value instanceof Money)) {
            $message = sprintf(
                'The "%s" constraint requires the "%s" options to be either "%s" or null',
                self::class,
                $optionName,
                Money::class,
            );
            $message .= sprintf(', "%s" given.', is_object($value) ? get_class($value) : gettype($value));

            throw new ConstraintDefinitionException($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
