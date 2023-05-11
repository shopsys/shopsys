<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ParameterTransactionFunctionalTestCase extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    protected ParameterFacade $parameterFacade;

    /**
     * @param string $parameterValueNameId
     * @return int
     */
    protected function getParameterValueIdForFirstDomain(string $parameterValueNameId): int
    {
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $parameterValueTranslatedName = t($parameterValueNameId, [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);

        return $this->parameterFacade->getParameterValueByValueTextAndLocale(
            $parameterValueTranslatedName,
            $firstDomainLocale
        )->getId();
    }
}
