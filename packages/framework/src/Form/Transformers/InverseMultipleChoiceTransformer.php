<?php

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Symfony\Component\Form\DataTransformerInterface;

class InverseMultipleChoiceTransformer implements DataTransformerInterface
{
    /**
     * @var array
     */
    protected $allChoices;

    /**
     * @param array $allChoices Choices from ChoiceType options
     */
    public function __construct(array $allChoices)
    {
        $this->allChoices = $allChoices;
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
     * @param array $inputValues
     * @return array
     */
    protected function getInvertedValues(array $inputValues)
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
