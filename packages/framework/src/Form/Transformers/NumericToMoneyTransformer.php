<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NumericToMoneyTransformer implements DataTransformerInterface
{
    /**
     * @var int
     */
    protected $floatScale;

    public function __construct(int $floatScale)
    {
        $this->floatScale = $floatScale;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $value
     * @return string|null
     */
    public function transform($value): ?string
    {
        if ($value === null) {
            return null;
        } elseif ($value instanceof Money) {
            return $value->toString();
        }

        throw new TransformationFailedException('Money or null must be provided.');
    }

    /**
     * @param string|float|int|null $value
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null $value
     */
    public function reverseTransform($value): ?Money
    {
        if ($value === null) {
            return null;
        } elseif (is_string($value)) {
            try {
                return Money::fromString($value);
            } catch (\Exception $e) {
                $message = sprintf('Unable to create Money from the string "%s".', $value);

                throw new TransformationFailedException($message, 0, $e);
            }
        } elseif (is_int($value)) {
            return Money::fromInteger($value);
        } elseif (is_float($value)) {
            return Money::fromFloat($value, $this->floatScale);
        }

        throw new TransformationFailedException('A string, a number or null must be provided.');
    }
}
