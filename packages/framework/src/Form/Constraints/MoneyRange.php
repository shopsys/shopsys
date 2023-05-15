<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use function get_class;
use function gettype;
use function is_object;

/**
 * @Annotation
 */
class MoneyRange extends Constraint
{
    public string $minMessage = 'The amount of money should be {{ limit }} or more.';

    public string $maxMessage = 'The amount of money should be {{ limit }} or less.';

    /**
     * array type is used for validation in JsFormValidator
     */
    public Money|array|null $min = null;

    /**
     * array type is used for validation in JsFormValidator
     */
    public Money|array|null $max = null;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->validateMoneyOrNullOption('min', $options);
        $this->validateMoneyOrNullOption('max', $options);

        parent::__construct($options);

        if ($this->min === null && $this->max === null) {
            $message = sprintf('Either option "min" or "max" must be given for constraint "%s".', self::class);

            throw new MissingOptionsException($message, ['min', 'max']);
        }
    }

    /**
     * @param string $optionName
     * @param array $options
     */
    protected function validateMoneyOrNullOption(string $optionName, array $options): void
    {
        if (!isset($options[$optionName])) {
            return;
        }

        $value = $options[$optionName];

        if ($value !== null && !($value instanceof Money)) {
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
}
