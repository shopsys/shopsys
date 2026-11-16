<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\OrderedForm;

use Shopsys\FormTypesBundle\OrderedForm\Exception\InvalidPositionException;
use Shopsys\FormTypesBundle\OrderedForm\Exception\OrderedFormException;

/**
 * Sorts form fields by their `position` option, see docs/extensibility/form-extension.md
 *
 * Taken over from Becklyn/OrderedFormBundle (BSD-3-Clause, Copyright (c) 2018, Becklyn Studios),
 * see open-source-license-acknowledgements-and-third-party-copyrights.md
 */
class FormFieldSorter
{
    public const string POSITION_FIRST = 'first';
    public const string POSITION_LAST = 'last';
    public const string DIRECTION_BEFORE = 'before';
    public const string DIRECTION_AFTER = 'after';

    /**
     * @var string[]
     */
    protected array $sortedFieldNames = [];

    /**
     * @var array<int, string[]>
     */
    protected array $fieldNamesByWeight = [];

    /**
     * @var string[]
     */
    protected array $notPositionedFieldNames = [];

    /**
     * @var string[]
     */
    protected array $lastFieldNames = [];

    /**
     * @var \Shopsys\FormTypesBundle\OrderedForm\RelativePosition[]
     */
    protected array $relativePositions = [];

    protected bool $isFrozen = false;

    /**
     * @param mixed $position value of the `position` form option
     */
    public function add(string $fieldName, mixed $position): void
    {
        if ($this->isFrozen) {
            throw new OrderedFormException("Can't add item after the form field sorter is frozen.");
        }

        if ($position === null) {
            $this->notPositionedFieldNames[] = $fieldName;
        } elseif (is_int($position)) {
            $this->fieldNamesByWeight[$position][] = $fieldName;
        } elseif ($position === self::POSITION_FIRST) {
            $this->sortedFieldNames[] = $fieldName;
        } elseif ($position === self::POSITION_LAST) {
            $this->lastFieldNames[] = $fieldName;
        } elseif (is_array($position)) {
            $this->addRelativePositioned($fieldName, $position);
        } else {
            throw new InvalidPositionException(sprintf('Invalid value for position of type %s', get_debug_type($position)));
        }
    }

    /**
     * @return string[]
     */
    public function getSortedFieldNames(): array
    {
        if (!$this->isFrozen) {
            $this->buildSortedFieldNames();
            $this->isFrozen = true;
        }

        return $this->sortedFieldNames;
    }

    /**
     * @param array<string, mixed> $position
     */
    protected function addRelativePositioned(string $fieldName, array $position): void
    {
        if (count($position) !== 1) {
            throw new InvalidPositionException('a position array must only contain exactly one entry.');
        }

        $referenceFieldName = reset($position);
        $direction = key($position);

        if (!is_string($referenceFieldName)) {
            throw new InvalidPositionException(sprintf("The referenced field name in position of '%s' must be a string.", $fieldName));
        }

        if ($referenceFieldName === $fieldName) {
            throw new InvalidPositionException(sprintf("A relative positioned form field can't reference itself, in '%s'", $fieldName));
        }

        if ($direction !== self::DIRECTION_BEFORE && $direction !== self::DIRECTION_AFTER) {
            throw new InvalidPositionException(sprintf("Unknown direction for position array: [position => '%s']", $direction));
        }

        // relatively positioned fields are first placed as not positioned and moved next to their reference field at the end
        $this->relativePositions[] = new RelativePosition(
            $fieldName,
            $direction === self::DIRECTION_BEFORE ? RelativePosition::BEFORE : RelativePosition::AFTER,
            $referenceFieldName,
        );
        $this->notPositionedFieldNames[] = $fieldName;
    }

    protected function buildSortedFieldNames(): void
    {
        foreach ($this->notPositionedFieldNames as $fieldName) {
            $this->sortedFieldNames[] = $fieldName;
        }

        ksort($this->fieldNamesByWeight);

        foreach ($this->fieldNamesByWeight as $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                $this->sortedFieldNames[] = $fieldName;
            }
        }

        foreach ($this->lastFieldNames as $fieldName) {
            $this->sortedFieldNames[] = $fieldName;
        }

        foreach ($this->relativePositions as $relativePosition) {
            $relativePosition->moveInList($this->sortedFieldNames);
        }
    }
}
