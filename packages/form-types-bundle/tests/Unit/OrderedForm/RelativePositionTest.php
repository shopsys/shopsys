<?php

declare(strict_types=1);

namespace Tests\FormTypesBundle\Unit\OrderedForm;

use PHPUnit\Framework\TestCase;
use Shopsys\FormTypesBundle\OrderedForm\Exception\InvalidPositionException;
use Shopsys\FormTypesBundle\OrderedForm\RelativePosition;

class RelativePositionTest extends TestCase
{
    public function testFieldIsMovedNextToTheReferenceField(): void
    {
        $fieldNames = ['item1', 'item2', 'item3'];

        $relativePosition = new RelativePosition('item1', RelativePosition::AFTER, 'item2');
        $result = $relativePosition->moveInList($fieldNames);

        self::assertSame(['item2', 'item1', 'item3'], $result);
        self::assertSame($result, $fieldNames);

        $relativePosition = new RelativePosition('item2', RelativePosition::BEFORE, 'item3');
        $result = $relativePosition->moveInList($fieldNames);

        self::assertSame(['item1', 'item2', 'item3'], $result);
    }

    public function testMissingReferenceFieldIsRejected(): void
    {
        $this->expectException(InvalidPositionException::class);

        $fieldNames = ['item1', 'item2', 'item3'];

        $relativePosition = new RelativePosition('item1', RelativePosition::AFTER, 'item4');
        $relativePosition->moveInList($fieldNames);
    }

    public function testFieldMissingInTheListIsInserted(): void
    {
        $fieldNames = ['item1', 'item2', 'item3'];

        $relativePosition = new RelativePosition('item4', RelativePosition::AFTER, 'item1');
        $result = $relativePosition->moveInList($fieldNames);

        self::assertSame(['item1', 'item4', 'item2', 'item3'], $result);
    }
}
