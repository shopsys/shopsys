<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TransferInvalidDataException extends TransferException
{
    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @param \Exception|null $previous
     */
    public function __construct(
        ConstraintViolationListInterface $violations,
        ?Exception $previous = null
    ) {
        $message = 'Data is not valid: ' . $this->getViolationsAsString($violations);
        parent::__construct($message, 0, $previous);
    }

    /**
     * @param string $message
     * @param string $propertyPath
     * @param \Exception|null $previous
     * @return \App\Component\Akeneo\Transfer\Exception\TransferInvalidDataException
     */
    public static function createWithViolation(string $message, string $propertyPath, ?Exception $previous = null): self
    {
        $violation = new ConstraintViolation(
            $message,
            '',
            [],
            '',
            $propertyPath,
            null
        );

        $violations = new ConstraintViolationList([$violation]);

        return new self($violations, $previous);
    }

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @return string
     */
    private function getViolationsAsString(ConstraintViolationListInterface $violations): string
    {
        $constraintsViolationsMessages = [];
        foreach ($violations as $violation) {
            $constraintsViolationsMessages[] =
                sprintf('Invalid value of %s - "%s"', $violation->getPropertyPath(), $violation->getMessage());
        }

        return implode(', ', $constraintsViolationsMessages);
    }
}
