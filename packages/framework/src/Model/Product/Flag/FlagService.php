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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function create(FlagData $flagData)
    {
        return $this->flagFactory->create($flagData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function edit(Flag $flag, FlagData $flagData)
    {
        $flag->edit($flagData);

        return $flag;
    }
}
