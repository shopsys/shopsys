<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class FlagDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
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

        foreach ($this->domain->getAllIds() as $domainId) {
            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, 'front_flag_detail', $flag->getId());
            $flagData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }
    }
}
