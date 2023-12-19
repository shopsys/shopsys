<?php

declare(strict_types=1);

namespace App\Model\Country;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\Country\CountryRepository as BaseCountryRepository;

class CountryRepository extends BaseCountryRepository
{
    /**
     * @param string $locale
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createSortedJoinedQueryBuilder(string $locale, int $domainId): QueryBuilder
    {
        $queryBuilder = parent::createSortedJoinedQueryBuilder($locale, $domainId);

        $queryBuilder
            ->orderBy('cd.priority', 'desc')
            ->addOrderBy(OrderByCollationHelper::createOrderByForLocale('ct.name', $locale), 'asc');

        return $queryBuilder;
    }
}
