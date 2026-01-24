<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CountryFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CountryRepository $countryRepository,
        protected readonly Domain $domain,
        protected readonly CountryFactory $countryFactory,
    ) {
    }

    public function getById(int $countryId): Country
    {
        return $this->countryRepository->getById($countryId);
    }

    public function create(CountryData $countryData): Country
    {
        $country = $this->countryFactory->create($countryData);
        $this->em->persist($country);
        $this->em->flush();

        return $country;
    }

    public function edit(int $countryId, CountryData $countryData): Country
    {
        $country = $this->countryRepository->getById($countryId);
        $country->edit($countryData);
        $this->em->flush();

        return $country;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAll(): array
    {
        return $this->countryRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllEnabledOnDomain(int $domainId): array
    {
        $localeByDomain = $this->domain->getDomainConfigById($domainId)->getLocale();

        return $this->countryRepository->getAllEnabledByDomainIdWithLocale($domainId, $localeByDomain);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllOnDomain(int $domainId): array
    {
        $localeByDomain = $this->domain->getDomainConfigById($domainId)->getLocale();

        return $this->countryRepository->getAllByDomainIdWithLocale($domainId, $localeByDomain);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllEnabledOnCurrentDomain(): array
    {
        return $this->countryRepository->getAllEnabledByDomainIdWithLocale(
            $this->domain->getId(),
            $this->domain->getLocale(),
        );
    }

    public function findByCode(string $countryCode): ?Country
    {
        return $this->countryRepository->findByCode($countryCode);
    }

    public function getCount(): int
    {
        return $this->countryRepository->getCount();
    }

    public function getByCode(string $countryCode): Country
    {
        return $this->countryRepository->getByCode($countryCode);
    }
}
