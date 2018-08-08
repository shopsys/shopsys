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
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function create(FlagData $flagData)
    {
        return $this->flagFactory->create($flagData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function edit(Flag $flag, FlagData $flagData)
    {
        $flag->edit($flagData);

        return $flag;
    }
}
