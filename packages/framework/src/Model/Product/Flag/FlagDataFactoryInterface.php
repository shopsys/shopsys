<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

interface FlagDataFactoryInterface
{
    public function create(): FlagData;

    public function createFromFlag(Flag $flag): FlagData;
}
