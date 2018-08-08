<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

interface UnitDataFactoryInterface
{
    public function create(): UnitData;

    public function createFromUnit(Unit $unit): UnitData;
}
