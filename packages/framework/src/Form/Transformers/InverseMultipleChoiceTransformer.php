<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Symfony\Component\Form\DataTransformerInterface;

class InverseMultipleChoiceTransformer implements DataTransformerInterface
{
    /**
     * @param array $allChoices Choices from ChoiceType options
     */
    public function __construct(protected readonly array $allChoices)
    {
    }

    #[Override]
    public function transform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return $this->getInvertedValues($value);
    }

    #[Override]
    public function reverseTransform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return $this->getInvertedValues($value);
    }

    protected function getInvertedValues(array $inputValues): array
    {
        $outputValues = [];

        foreach ($this->allChoices as $choice) {
            if (!in_array($choice, $inputValues, true)) {
                $outputValues[] = $choice;
            }
        }

        return $outputValues;
    }
}
