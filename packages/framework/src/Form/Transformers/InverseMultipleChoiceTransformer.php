<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @template T
 */
class InverseMultipleChoiceTransformer implements DataTransformerInterface
{
    /**
     * @var T[]
     */
    protected $allChoices;

    /**
     * @param T[] $allChoices Choices from ChoiceType options
     */
    public function __construct(array $allChoices)
    {
        $this->allChoices = $allChoices;
    }

    /**
     * {@inheritDoc}
     * @return T[]|null
     */
    public function transform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return $this->getInvertedValues($value);
    }

    /**
     * {@inheritDoc}
     * @return T[]|null
     */
    public function reverseTransform($value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        return $this->getInvertedValues($value);
    }

    /**
     * @param T[] $inputValues
     * @return T[]
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
