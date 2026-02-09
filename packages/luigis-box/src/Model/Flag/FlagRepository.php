<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Flag;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository as BaseFlagRepository;

class FlagRepository
{
    public function __construct(
        protected readonly BaseFlagRepository $flagRepository,
        protected readonly Domain $domain,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
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
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('ft.name', $this->domain->getLocale()), 'asc')
            ->setParameter('flagNames', $flagNames)
            ->setParameter('locale', $this->domain->getLocale());

        return $queryBuilder->getQuery()->getResult();
    }
}
