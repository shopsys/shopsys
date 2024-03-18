<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Flag;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository as BaseFlagRepository;

class FlagRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository $flagRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BaseFlagRepository $flagRepository,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string[] $flagNames
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlagsByNames(array $flagNames): array
    {
        $queryBuilder = $this->flagRepository->getVisibleQueryBuilder()
            ->addSelect('ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('ft.name IN (:flagNames)')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $this->domain->getLocale()), 'asc')
            ->setParameter('flagNames', $flagNames)
            ->setParameter('locale', $this->domain->getLocale());

        return $queryBuilder->getQuery()->getResult();
    }
}
