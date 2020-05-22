<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use function GuzzleHttp\json_encode as json_encode;

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
        $parameterValueIdByParameterId = array_map('intval', $parameterValueIdByParameterId);

        if (count($flagIds) === 1) {
            $flagId = (int)array_shift($flagIds);
        } else {
            $flagId = null;
        }

        $combinationArray = ChoseCategorySeoMixCombination::getChoseCategorySeoMixCombinationArray(
            $domainConfig->getId(),
            $categoryId,
            $flagId,
            $ordering,
            $parameterValueIdByParameterId
        );

        $readyCategorySeoMix = $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.choseCategorySeoMixCombinationJson = :combinationJson')
            ->setParameter('combinationJson', json_encode($combinationArray))
            ->getQuery()
            ->getOneOrNullResult();

        if ($readyCategorySeoMix === null) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: no exact match by product filter form and ordering'
            );
        }

        return $readyCategorySeoMix;
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
}
