<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Document;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\SlotFitter;

class SlotFitterTest extends TestCase
{
    public function testExtraSlotsAreAppendedToLastKeptSlot(): void
    {
        $this->assertSame([['a'], ['b', 'c', 'd']], SlotFitter::fit([['a'], ['b'], ['c'], ['d']], 2));
    }

    public function testMissingSlotsAreAddedEmpty(): void
    {
        $this->assertSame([['a'], [], []], SlotFitter::fit([['a']], 3));
    }

    public function testSlotsAreReindexed(): void
    {
        $this->assertSame([['a'], ['b']], SlotFitter::fit([3 => ['a'], 7 => ['b']], 2));
    }
}
