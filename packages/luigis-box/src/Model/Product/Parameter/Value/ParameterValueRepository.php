<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product\Parameter\Value;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterValueRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param string[] $parameterValues
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getExistingParameterValuesByValuesAndLocale(array $parameterValues, string $locale): array
    {
        return $this->em->createQueryBuilder()
            ->select('pv')
            ->from(ParameterValue::class, 'pv')
            ->where('pv.text IN(:parameterValues)')
            ->andWhere('pv.locale = :locale')
            ->setParameters([
                'parameterValues' => $parameterValues,
                'locale' => $locale,
            ])
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('pv.text', $locale))->getQuery()->getResult();
    }

    /**
     * @param string[] $parameterValues,
     * @param string $locale
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
            ->setParameters([
                'parameterValues' => $parameterValues,
                'locale' => $locale,
            ])
            ->getQuery()->getResult();
    }
}
