<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Document;

use Webmozart\Assert\Assert;

/**
 * Fits the slots of a container to the number of slots its layout has. Works on any lists of items.
 */
final class SlotFitter
{
    /**
     * Keeps the first slots, appends items of the extra slots to the last kept one and adds missing slots empty
     *
     * @template T
     * @param array<int, array<int, T>> $slots
     * @return array<int, array<int, T>>
     */
    public static function fit(array $slots, int $slotCount): array
    {
        Assert::positiveInteger($slotCount);

        $slots = array_values($slots);
        $fittedSlots = array_slice($slots, 0, $slotCount);

        foreach (array_slice($slots, $slotCount) as $extraSlot) {
            $fittedSlots[$slotCount - 1] = [...$fittedSlots[$slotCount - 1], ...$extraSlot];
        }

        return array_pad($fittedSlots, $slotCount, []);
    }
}
