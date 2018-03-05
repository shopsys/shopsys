<?php

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\EntityManager;

class CountryRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getCountryRepository()
    {
        return $this->em->getRepository(Country::class);
    }

    /**
     * @param int $countryId
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function findById($countryId)
    {
        return $this->getCountryRepository()->find($countryId);
    }

    /**
     * @param int $countryId
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public function getById($countryId)
    {
        $country = $this->findById($countryId);

        if ($country === null) {
            throw new \Shopsys\FrameworkBundle\Model\Country\Exception\CountryNotFoundException('Country with ID ' . $countryId . ' not found.');
        }

        return $country;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllByDomainId($domainId)
    {
        return $this->getCountryRepository()->findBy(['domainId' => $domainId], ['id' => 'asc']);
    }
}
