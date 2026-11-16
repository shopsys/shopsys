<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\OrderedForm;

use Shopsys\FormTypesBundle\OrderedForm\Exception\InvalidPositionException;

/**
 * Position of a form field relative to another field, see `position` option in docs/extensibility/form-extension.md
 *
 * Taken over from Becklyn/OrderedFormBundle (BSD-3-Clause, Copyright (c) 2018, Becklyn Studios),
 * see open-source-license-acknowledgements-and-third-party-copyrights.md
 */
class RelativePosition
{
    public const bool BEFORE = true;
    public const bool AFTER = false;

    public function __construct(
        protected readonly string $fieldName,
        protected readonly bool $isBefore,
        protected readonly string $referenceFieldName,
    ) {
    }

    /**
     * Moves the field next to its reference field, the list is modified in place and also returned
     *
     * @param string[] $fieldNames
     * @return string[]
     */
    public function moveInList(array &$fieldNames): array
    {
        $this->removeFromList($fieldNames);
        $referenceIndex = array_search($this->referenceFieldName, $fieldNames, true);

        if ($referenceIndex === false) {
            throw new InvalidPositionException(sprintf(
                "Can't position field '%s' relative to '%s', as the field wasn't found.",
                $this->fieldName,
                $this->referenceFieldName,
            ));
        }

        if (!$this->isBefore) {
            $referenceIndex++;
        }

        array_splice($fieldNames, $referenceIndex, 0, [$this->fieldName]);

        return $fieldNames;
    }

    /**
     * @param string[] $fieldNames
     */
    protected function removeFromList(array &$fieldNames): void
    {
        $currentIndex = array_search($this->fieldName, $fieldNames, true);

        if ($currentIndex === false) {
            return;
        }

        array_splice($fieldNames, $currentIndex, 1);
    }
}
