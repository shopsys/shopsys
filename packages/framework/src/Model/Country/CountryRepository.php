<?php

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\EntityManagerInterface;

class CountryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getCountryRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Country::class);
    }

    public function findById(int $countryId): ?\Shopsys\FrameworkBundle\Model\Country\Country
    {
        return $this->getCountryRepository()->find($countryId);
    }

    public function getById(int $countryId): \Shopsys\FrameworkBundle\Model\Country\Country
    {
        $country = $this->findById($countryId);

        if ($country === null) {
            throw new \Shopsys\FrameworkBundle\Model\Country\Exception\CountryNotFoundException('Country with ID ' . $countryId . ' not found.');
        }

        return $country;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getCountryRepository()->findBy(['domainId' => $domainId], ['id' => 'asc']);
    }
}
