<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

interface FlagFactoryInterface
{
    public function create(FlagData $data): Flag;
}
