<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ParameterTransactionFunctionalTestCase extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     * @inject
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     * @inject
     */
    private $parameterFacade;

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
