<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData as BaseFlagData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory as BaseFlagDataFactory;

/**
 * @method fillNew(\App\Model\Product\Flag\FlagData $flagData)
 * @property \App\Model\Product\Flag\Flag $flag
 * @property \App\Model\Product\Flag\FlagData $flagData
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 */
class FlagDataFactory extends BaseFlagDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(Domain $domain, private FriendlyUrlFacade $friendlyUrlFacade)
    {
        parent::__construct($domain);
    }

    /**
     * @return \App\Model\Product\Flag\FlagData
     */
    protected function createInstance(): BaseFlagData
    {
        return new FlagData();
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     * @param \App\Model\Product\Flag\Flag $flag
     */
    protected function fillFromFlag(BaseFlagData $flagData, Flag $flag): void
    {
        parent::fillFromFlag($flagData, $flag);

        $flagData->akeneoCode = $flag->getAkeneoCode();

        foreach ($this->domain->getAllIds() as $domainId) {
            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, 'front_flag_detail', $flag->getId());
            $flagData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \App\Model\Product\Flag\FlagData
     */
    public function createFromFlag(Flag $flag): BaseFlagData
    {
        $flagData = new FlagData();
        $this->fillFromFlag($flagData, $flag);

        return $flagData;
    }

    /**
     * @return \App\Model\Product\Flag\FlagData
     */
    public function create(): BaseFlagData
    {
        $flagData = new FlagData();
        $this->fillNew($flagData);

        return $flagData;
    }
}
