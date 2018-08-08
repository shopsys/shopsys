<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

interface UnitFactoryInterface
{
    public function create(UnitData $data): Unit;
}
