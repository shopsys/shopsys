<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product\Parameter\Value;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    /**
     * @param string[] $parameterValues
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getExistingParameterValuesByValuesAndLocale(array $parameterValues, string $locale): array
    {
        return $this->em->createQueryBuilder()
            ->select('pv')
            ->from(ParameterValue::class, 'pv')
            ->where('pv.text IN(:parameterValues)')
            ->andWhere('pv.locale = :locale')
            ->setParameter('parameterValues', $parameterValues)
            ->setParameter('locale', $locale)
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('pv.text', $locale))->getQuery()->getResult();
    }

    /**
     * @param string[] $parameterValues,
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getSliderParameterValuesForMinAndMaxByLocale(
        array $parameterValues,
        string $locale,
    ): array {
        return $this->em->createQueryBuilder()
            ->select('pv')
            ->from(ParameterValue::class, 'pv')
            ->where('pv.numericValue IN(:parameterValues)')
            ->andWhere('pv.locale = :locale')
            ->setParameter('parameterValues', $parameterValues)
            ->setParameter('locale', $locale)
            ->getQuery()->getResult();
    }
}
