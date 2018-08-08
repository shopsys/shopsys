<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagFactory implements FlagFactoryInterface
{

    public function create(FlagData $data): Flag
    {
        return new Flag($data);
    }
}
