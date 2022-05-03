<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * This class was removed from PHP CS Fixer and moved to PHP-CS-Fixer/phpunit-constraint-isidenticalstring package,
 * which install phpunitgoodpractices/polyfill and brakes codeception...
 */
class IsIdenticalString extends Constraint
{
    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * @var \PHPUnit\Framework\Constraint\IsIdentical
     */
    private IsIdentical $isIdentical;

    /**
     * @param mixed $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
        $this->isIdentical = new IsIdentical($this->value);
    }

    /**
     * @param mixed $other
     * @param string $description
     * @param bool $returnResult
     * @return bool|null
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        try {
            return $this->isIdentical->evaluate($other, $description, $returnResult);
        } catch (ExpectationFailedException $exception) {
            $message = $exception->getMessage();

            $additionalFailureDescription = $this->additionalFailureDescription($other);

            if ($additionalFailureDescription) {
                $message .= "\n" . $additionalFailureDescription;
            }

            throw new ExpectationFailedException(
                $message,
                $exception->getComparisonFailure(),
                $exception
            );
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->isIdentical->toString();
    }

    /**
     * @param mixed $other
     * @return string
     */
    protected function additionalFailureDescription($other): string
    {
        $pattern = '/(\r\n|\n\r|\r)/';

        if (
            $other === $this->value ||
            preg_replace($pattern, "\n", $other) !== preg_replace($pattern, "\n", $this->value)
        ) {
            return '';
        }

        return ' #Warning: Strings contain different line endings! Debug using remapping ["\r" => "R", "\n" => "N", "\t" => "T"]:'
            . "\n"
            . ' -' . str_replace(["\r", "\n", "\t"], ['R', 'N', 'T'], $other)
            . "\n"
            . ' +' . str_replace(["\r", "\n", "\t"], ['R', 'N', 'T'], $this->value);
    }
}
