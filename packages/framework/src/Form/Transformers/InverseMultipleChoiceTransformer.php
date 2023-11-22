<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class InverseMultipleChoiceTransformer implements DataTransformerInterface
{
    /**
     * @param mixed[] $allChoices Choices from ChoiceType options
     */
    public function __construct(protected readonly array $allChoices)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return $this->getInvertedValues($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return $this->getInvertedValues($value);
    }

    /**
     * @param mixed[] $inputValues
     * @return mixed[]
     */
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
