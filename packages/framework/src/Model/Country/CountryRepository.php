<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Model\Country\Exception\CountryNotFoundException;

class CountryRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    protected function getCountryRepository(): EntityRepository
    {
        return $this->em->getRepository(Country::class);
    }

    public function createSortedJoinedQueryBuilder(string $locale, int $domainId): QueryBuilder
    {
        return $this->getCountryRepository()->createQueryBuilder('c')
            ->join('c.domains', 'cd', Join::WITH, 'cd.domainId = :domainId')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->orderBy('cd.priority', 'desc')
            ->addOrderBy($this->orderByCollationHelper->createOrderByForLocale('ct.name', $locale), 'asc')
            ->setParameter('locale', $locale)
            ->setParameter('domainId', $domainId);
    }

    public function findById(int $countryId): ?Country
    {
        return $this->getCountryRepository()->find($countryId);
    }

    public function getById(int $countryId): Country
    {
        $country = $this->findById($countryId);

        if ($country === null) {
            throw new CountryNotFoundException('Country with ID ' . $countryId . ' not found.');
        }

        return $country;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAll(): array
    {
        return $this->getCountryRepository()->findAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllEnabledByDomainIdWithLocale(int $domainId, string $locale): array
    {
        return $this->createSortedJoinedQueryBuilder($locale, $domainId)
            ->where('cd.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class)
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function getAllByDomainIdWithLocale(int $domainId, string $locale): array
    {
        return $this->createSortedJoinedQueryBuilder($locale, $domainId)
            ->getQuery()
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class)
            ->getResult();
    }

    public function findByCode(string $countryCode): ?Country
    {
        return $this->getCountryRepository()->findOneBy(['code' => $countryCode]);
    }

    public function getCount(): int
    {
        return $this->getCountryRepository()->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getByCode(string $countryCode): Country
    {
        $country = $this->findByCode($countryCode);

        if ($country === null) {
            throw new CountryNotFoundException('Country with code ' . $countryCode . ' not found.');
        }

        return $country;
    }
}
