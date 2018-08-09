<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagDataFactory implements FlagDataFactoryInterface
{
    public function create(): FlagData
    {
        return new FlagData();
    }

    public function createFromFlag(Flag $flag): FlagData
    {
        $flagData = new FlagData();
        $this->fillFromFlag($flagData, $flag);

        return $flagData;
    }

    protected function fillFromFlag(FlagData $flagData, Flag $flag)
    {
        $translations = $flag->getTranslations();
        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $flagData->name = $names;
        $flagData->rgbColor = $flag->getRgbColor();
        $flagData->visible = $flag->isVisible();
    }
}
