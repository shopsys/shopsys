<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ParameterTransactionFunctionalTestCase extends TransactionFunctionalTestCase
{
    /**
     * @param string $parameterValueNameId
     * @return int
     */
    protected function getParameterValueIdForFirstDomain(string $parameterValueNameId): int
    {
        /** @var \Shopsys\FrameworkBundle\Component\Domain\Domain $domain */
        $domain = $this->getContainer()->get(Domain::class);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade */
        $parameterFacade = $this->getContainer()->get(ParameterFacade::class);

        $firstDomainLocale = $domain->getDomainConfigById(1)->getLocale();
        $parameterValueTranslatedName = t($parameterValueNameId, [], 'dataFixtures', $firstDomainLocale);

        return $parameterFacade->getParameterValueByValueTextAndLocale($parameterValueTranslatedName, $firstDomainLocale)->getId();
    }
}
