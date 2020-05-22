<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
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
     * @param int $categoryId
     * @param array $parameterValueIdsByParameterId
     * @param int[] $flagIds
     * @param string|null $ordering
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix
     */
    public function getReadyCategorySeoMixFromFilter(
        int $categoryId,
        array $parameterValueIdsByParameterId,
        array $flagIds,
        ?string $ordering,
        DomainConfig $domainConfig
    ): ReadyCategorySeoMix {
        $this->checkPossibilityToFindReadyCategorySeoMix($parameterValueIdsByParameterId, $flagIds, $ordering);
        $parameterValueIdsByParameterId = array_filter($parameterValueIdsByParameterId);
        $parameterValueIdByParameterId = array_map('array_shift', $parameterValueIdsByParameterId);

        $readyCategorySeoMixesQueryBuilder = $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.category = :categoryId')->setParameter('categoryId', $categoryId)
            ->andWhere('rcsm.domainId = :domainId')->setParameter('domainId', $domainConfig->getId())
            ->groupBy('rcsm.id');

        $this->addParametersToFilterFormQueryBuilder($readyCategorySeoMixesQueryBuilder, $parameterValueIdByParameterId);
        $this->addFlagsToFilterFormQueryBuilder($readyCategorySeoMixesQueryBuilder, $flagIds);
        $this->addOrderingModeToFilterFormQueryBuilder($readyCategorySeoMixesQueryBuilder, $ordering);

        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix[] $readyCategorySeoMixes */
        $readyCategorySeoMixes = $readyCategorySeoMixesQueryBuilder->getQuery()->execute();

        $countOfChosenParameters = count($parameterValueIdByParameterId);
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

        return array_shift($readyCategorySeoMixes);
    }

    /**
     * @param array $parameterValueIdsByParameterId
     * @param int[] $flagIds
     * @param string|null $ordering
     */
    private function checkPossibilityToFindReadyCategorySeoMix(
        array $parameterValueIdsByParameterId,
        array $flagIds,
        ?string $ordering
    ): void {
        if ($ordering === null && count($parameterValueIdsByParameterId) === 0 && count($flagIds)) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: it cannot have anything for conditions'
            );
        }

        foreach ($parameterValueIdsByParameterId as $parameterValueIds) {
            if (count($parameterValueIds) > 1) {
                throw new UnableToFindReadyCategorySeoMixException(
                    'Unable to find ReadyCategorySeoMix: it cannot have more than one parameter value of one parameter'
                );
            }
        }

        if (count($flagIds) > 1) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: it cannot have more than one flag'
            );
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $readyCategorySeoMixesQueryBuilder
     * @param string|null $ordering
     */
    private function addOrderingModeToFilterFormQueryBuilder(
        QueryBuilder $readyCategorySeoMixesQueryBuilder,
        ?string $ordering
    ): void {
        if ($ordering === null) {
            $readyCategorySeoMixesQueryBuilder->andWhere('rcsm.ordering IS NULL');
        } else {
            $readyCategorySeoMixesQueryBuilder->andWhere('rcsm.ordering = :ordering')->setParameter('ordering', $ordering);
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $readyCategorySeoMixesQueryBuilder
     * @param int[] $flagIds
     */
    private function addFlagsToFilterFormQueryBuilder(
        QueryBuilder $readyCategorySeoMixesQueryBuilder,
        array $flagIds
    ): void {
        if (count($flagIds) === 1) {
            $flagId = array_shift($flagIds);
            $readyCategorySeoMixesQueryBuilder
                ->andWhere('rcsm.flag = :flagId')->setParameter('flagId', $flagId);
        } else {
            $readyCategorySeoMixesQueryBuilder->andWhere('rcsm.flag IS NULL');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $readyCategorySeoMixesQueryBuilder
     * @param array $parameterValueIdByParameterId
     */
    private function addParametersToFilterFormQueryBuilder(
        QueryBuilder $readyCategorySeoMixesQueryBuilder,
        array $parameterValueIdByParameterId
    ): void {
        foreach ($parameterValueIdByParameterId as $parameterId => $parameterValueId) {
            $parameterId = (int)$parameterId;
            $parameterValueId = (int)$parameterValueId;

            $builderParameterName = 'parameter_' . $parameterId;
            $builderParameterValueName = 'parameterValue_' . $parameterId;
            $readyCategorySeoMixesQueryBuilder->setParameter($builderParameterName, $parameterId);
            $readyCategorySeoMixesQueryBuilder->setParameter($builderParameterValueName, $parameterValueId);

            $alias = sprintf('rcsmppv_%s_%s', $parameterId, $parameterValueId);
            $readyCategorySeoMixesQueryBuilder->innerJoin(
                ReadyCategorySeoMixParameterParameterValue::class,
                $alias,
                Join::WITH,
                sprintf(
                    'rcsm = %s.readyCategorySeoMix AND %s.parameter = :%s AND %s.parameterValue = :%s',
                    $alias,
                    $alias,
                    $builderParameterName,
                    $alias,
                    $builderParameterValueName
                )
            );
        }
    }
}
