<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class FlagDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    protected function createInstance(): FlagData
    {
        return new FlagData();
    }

    public function create(): FlagData
    {
        $flagData = $this->createInstance();
        $this->fillNew($flagData);

        return $flagData;
    }

    protected function fillNew(FlagData $flagData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $flagData->name[$locale] = null;
        }
    }

    public function createFromFlag(Flag $flag): FlagData
    {
        $flagData = $this->createInstance();
        $this->fillFromFlag($flagData, $flag);

        return $flagData;
    }

    protected function fillFromFlag(FlagData $flagData, Flag $flag): void
    {
        $translations = $flag->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $flagData->name = $names;
        $flagData->rgbColor = $flag->getRgbColor();
        $flagData->visible = $flag->isVisible();
        $flagData->uuid = $flag->getUuid();
        $flagData->promotionXy = $flag->getPromotionXy();

        foreach ($this->domain->getAllIds() as $domainId) {
            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, 'front_flag_detail', $flag->getId());
            $flagData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }
    }
}
