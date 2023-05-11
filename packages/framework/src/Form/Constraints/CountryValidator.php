<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CountryValidator extends ConstraintValidator
{
    protected CountryFacade $countryFacade;

    protected Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CountryFacade $countryFacade,
        Domain $domain
    ) {
        $this->domain = $domain;
        $this->countryFacade = $countryFacade;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Country) {
            throw new UnexpectedTypeException($constraint, Country::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        $country = (string)$value;

        $domainId = $constraint->domainId ?? $this->domain->getId();

        $availableCountryCodes = [];
        $countriesOnDomain = $this->countryFacade->getAllEnabledOnDomain($domainId);

        foreach ($countriesOnDomain as $countryOnDomain) {
            $availableCountryCodes[] = $countryOnDomain->getCode();
        }

        if (in_array($country, $availableCountryCodes, true)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ country_code }}', $country)
            ->setParameter('{{ available_country_codes }}', implode(', ', $availableCountryCodes))
            ->setCode(Country::INVALID_COUNTRY_ERROR)
            ->addViolation();
    }
}
