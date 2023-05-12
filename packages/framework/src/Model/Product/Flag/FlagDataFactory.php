<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagDataFactory implements FlagDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData
     */
    protected function createInstance(): FlagData
    {
        return new FlagData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData
     */
    public function create(): FlagData
    {
        $flagData = $this->createInstance();
        $this->fillNew($flagData);

        return $flagData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     */
    protected function fillNew(FlagData $flagData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $flagData->name[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData
     */
    public function createFromFlag(Flag $flag): FlagData
    {
        $flagData = $this->createInstance();
        $this->fillFromFlag($flagData, $flag);

        return $flagData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     */
    protected function fillFromFlag(FlagData $flagData, Flag $flag)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation[] $translations */
        $translations = $flag->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $flagData->name = $names;
        $flagData->rgbColor = $flag->getRgbColor();
        $flagData->visible = $flag->isVisible();
        $flagData->uuid = $flag->getUuid();
    }
}
