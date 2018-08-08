<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

class UnitFactory implements UnitFactoryInterface
{
    public function create(UnitData $data): Unit
    {
        return new Unit($data);
    }
}
