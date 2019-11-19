<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ParameterTransactionFunctionalTestCase extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     * @inject
     */
    protected $parameterFacade;

    /**
     * @param string $parameterValueNameId
     * @return int
     */
    protected function getParameterValueIdForFirstDomain(string $parameterValueNameId): int
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $parameterValueTranslatedName = t($parameterValueNameId, [], 'dataFixtures', $firstDomainLocale);

        return $this->parameterFacade->getParameterValueByValueTextAndLocale($parameterValueTranslatedName, $firstDomainLocale)->getId();
    }
}
