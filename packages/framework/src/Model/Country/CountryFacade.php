<?php

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CountryFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryRepository
     */
    protected $countryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFactoryInterface
     */
    protected $countryFactory;

    public function __construct(
        EntityManagerInterface $em,
        CountryRepository $countryRepository,
        Domain $domain,
        CountryFactoryInterface $countryFactory
    ) {
        $this->em = $em;
        $this->countryRepository = $countryRepository;
        $this->domain = $domain;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param int $countryId
     */
    public function getById($countryId): \Shopsys\FrameworkBundle\Model\Country\Country
    {
        return $this->countryRepository->getById($countryId);
    }

    /**
     * @param int $domainId
     */
    public function create(CountryData $countryData, $domainId): \Shopsys\FrameworkBundle\Model\Country\Country
    {
        $country = $this->countryFactory->create($countryData, $domainId);
        $this->em->persist($country);
        $this->em->flush($country);

        return $country;
    }

    /**
     * @param int $countryId
     */
    public function edit($countryId, CountryData $countryData): \Shopsys\FrameworkBundle\Model\Country\Country
    {
        $country = $this->countryRepository->getById($countryId);
        $country->edit($countryData);
        $this->em->flush($country);

        return $country;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllByDomainId($domainId): array
    {
        return $this->countryRepository->getAllByDomainId($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllOnCurrentDomain(): array
    {
        return $this->countryRepository->getAllByDomainId($this->domain->getId());
    }
}
