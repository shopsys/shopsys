<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\Category\Category;
use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ReadyCategorySeoMixRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository(): ObjectRepository
    {
        return $this->em->getRepository(ReadyCategorySeoMix::class);
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findByChoseCategorySeoMixCombination(ChoseCategorySeoMixCombination $choseCategorySeoMixCombination): ?ReadyCategorySeoMix
    {
        return $this->getRepository()->findOneBy([
            'choseCategorySeoMixCombinationJson' => $choseCategorySeoMixCombination->getInJson(),
        ]);
    }

    /**
     * @param int $id
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findById(int $id): ?ReadyCategorySeoMix
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix[]
     */
    public function getAllWithParameter(Parameter $parameter): array
    {
        return $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->join(ReadyCategorySeoMixParameterParameterValue::class, 'ppv', Join::WITH, 'ppv.readyCategorySeoMix = rcsm')
            ->where('ppv.parameter = :parameter')
            ->setParameters([
                'parameter' => $parameter,
            ])
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $ordering
     * @param int $domainId
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function getReadyCategorySeoMixFromFilter(
        Category $category,
        ProductFilterData $productFilterData,
        string $ordering,
        int $domainId
    ): ReadyCategorySeoMix {
        $this->checkPossibilityToFindReadyCategorySeoMix($productFilterData);

        $readyCategorySeoMixesQueryBuilder = $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->join('rcsm.readyCategorySeoMixParameterParameterValues', 'rcsmppv')
            ->andWhere('rcsm.category = :category')->setParameter('category', $category)
            ->andWhere('rcsm.domainId = :domainId')->setParameter('domainId', $domainId)
            ->andWhere('rcsm.ordering = :ordering OR rcsm.ordering IS NULL')->setParameter('ordering', $ordering)
            ->groupBy('rcsm.id');

        $this->addFlagsToFilterFormQueryBuilder($readyCategorySeoMixesQueryBuilder, $productFilterData);
        $this->addParametersToFilterFormQueryBuilder($readyCategorySeoMixesQueryBuilder, $productFilterData);

        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix[] $readyCategorySeoMixes */
        $readyCategorySeoMixes = $readyCategorySeoMixesQueryBuilder->getQuery()->execute();

        $countOfChosenParameters = 0;
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if (count($parameterFilterData->values) === 1) {
                $countOfChosenParameters++;
            }
        }

        foreach ($readyCategorySeoMixes as $index => $readyCategorySeoMix) {
            if ($readyCategorySeoMix->countReadyCategorySeoMixParameterParameterValues() > $countOfChosenParameters) {
                unset($readyCategorySeoMixes[$index]);
            }
        }

        if (count($readyCategorySeoMixes) < 1) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: no exact match by product filter form and ordering'
            );
        }

        usort(
            $readyCategorySeoMixes,
            function (ReadyCategorySeoMix $readyCategorySeoMix1, ReadyCategorySeoMix $readyCategorySeoMix2) use ($ordering) {
                return $readyCategorySeoMix1->getOrdering() === $ordering ? -1 : 1;
            }
        );

        return array_shift($readyCategorySeoMixes);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @throws \App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException
     */
    private function checkPossibilityToFindReadyCategorySeoMix(ProductFilterData $productFilterData): void
    {
        foreach ($productFilterData->parameters as $parameterFilterData) {
            if (count($parameterFilterData->values) > 1) {
                throw new UnableToFindReadyCategorySeoMixException(
                    'Unable to find ReadyCategorySeoMix: it cannot have more than one parameter value of one parameter'
                );
            }
        }
        if (count($productFilterData->flags) > 1) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: it cannot have more than one flag'
            );
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $readyCategorySeoMixesQueryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     */
    private function addFlagsToFilterFormQueryBuilder(
        QueryBuilder $readyCategorySeoMixesQueryBuilder,
        ProductFilterData $productFilterData
    ): void {
        if (count($productFilterData->flags) === 1) {
            $flag = array_shift($productFilterData->flags);
            $readyCategorySeoMixesQueryBuilder
                ->andWhere('rcsm.flag = :flag')->setParameter('flag', $flag);
        } else {
            $readyCategorySeoMixesQueryBuilder->andWhere('rcsm.flag IS NULL');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $readyCategorySeoMixesQueryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     */
    private function addParametersToFilterFormQueryBuilder(
        QueryBuilder $readyCategorySeoMixesQueryBuilder,
        ProductFilterData $productFilterData
    ): void {
        $builderParameterParameters = [];
        $builderParameterParameterValues = [];

        foreach ($productFilterData->parameters as $parameterFilterData) {
            if (count($parameterFilterData->values) < 1) {
                continue;
            }

            $parameter = $parameterFilterData->parameter;
            $builderParameterParameterName = 'parameter_' . $parameter->getId();

            foreach ($parameterFilterData->values as $parameterValue) {
                $builderParameterParameters[$builderParameterParameterName] = $parameter;

                $builderParameterParameterValueName = 'parameterValue_' . $parameter->getId();
                $builderParameterParameterValues[$builderParameterParameterValueName] = $parameterValue;

                $alias = sprintf('rcsmppv_%s_%s', $parameter->getId(), $parameterValue->getId());
                $readyCategorySeoMixesQueryBuilder->innerJoin(
                    ReadyCategorySeoMixParameterParameterValue::class,
                    $alias,
                    Join::WITH,
                    sprintf(
                        'rcsm = %s.readyCategorySeoMix AND %s.parameter = :%s AND %s.parameterValue = :%s',
                        $alias,
                        $alias,
                        $builderParameterParameterName,
                        $alias,
                        $builderParameterParameterValueName
                    )
                );
            }
        }

        foreach ($builderParameterParameters as $builderParameterName => $parameter) {
            $readyCategorySeoMixesQueryBuilder->setParameter($builderParameterName, $parameter);
        }

        foreach ($builderParameterParameterValues as $builderParameterValueName => $parameterValue) {
            $readyCategorySeoMixesQueryBuilder->setParameter($builderParameterValueName, $parameterValue);
        }
    }
}
