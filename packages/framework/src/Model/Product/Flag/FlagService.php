<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagService
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFactoryInterface
     */
    protected $flagFactory;

    public function __construct(FlagFactoryInterface $flagFactory)
    {
        $this->flagFactory = $flagFactory;
    }

    public function create(FlagData $flagData): \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        return $this->flagFactory->create($flagData);
    }

    public function edit(Flag $flag, FlagData $flagData): \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        $flag->edit($flagData);

        return $flag;
    }
}
