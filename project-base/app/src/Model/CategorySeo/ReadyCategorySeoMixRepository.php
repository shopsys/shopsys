<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Component\Doctrine\OrderByCollationHelper;
use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ObjectRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Symfony\Contracts\Service\ResetInterface;
use function GuzzleHttp\json_encode;

class ReadyCategorySeoMixRepository implements ResetInterface
{
    /**
     * @var string[][][]
     */
    private array $readySeoCategorySetup;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        $this->readySeoCategorySetup = [];
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository
     */
    private function getRepository(): ObjectRepository
    {
        return $this->em->getRepository(ReadyCategorySeoMix::class);
    }

    /**
     * @param \App\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findByChoseCategorySeoMixCombination(
        ChoseCategorySeoMixCombination $choseCategorySeoMixCombination,
    ): ?ReadyCategorySeoMix {
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
     * @param array<int,int> $parameterValueIdsByParameterId
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
        DomainConfig $domainConfig,
    ): ReadyCategorySeoMix {
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
            $parameterValueIdsByParameterId,
        );

        $combinationJson = json_encode($combinationArray);

        if ($this->isJsonCombinationSeoCategory($categoryId, $domainConfig->getId(), $combinationJson) === false) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: no exact match by product filter form and ordering',
            );
        }

        $readyCategorySeoMix = $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.choseCategorySeoMixCombinationJson = :combinationJson')
            ->setParameter('combinationJson', $combinationJson)
            ->getQuery()
            ->getOneOrNullResult();

        if ($readyCategorySeoMix === null) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: no exact match by product filter form and ordering',
            );
        }

        return $readyCategorySeoMix;
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @param string $combinationJson
     * @return bool
     */
    private function isJsonCombinationSeoCategory(int $categoryId, int $domainId, string $combinationJson): bool
    {
        $readySeoCategorySetup = $this->getReadySeoCategorySetupFromLocalCache($categoryId, $domainId);

        return in_array($combinationJson, $readySeoCategorySetup, true);
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @return string[]
     */
    private function getReadySeoCategorySetupFromLocalCache(int $categoryId, int $domainId): array
    {
        if (($this->readySeoCategorySetup[$domainId][$categoryId] ?? null) === null) {
            $scalarData = $this->em->createQueryBuilder()
                ->select('rcsm.choseCategorySeoMixCombinationJson as json')
                ->from(ReadyCategorySeoMix::class, 'rcsm')
                ->where('IDENTITY(rcsm.category) = :categoryId')
                ->andWhere('rcsm.domainId = :domainId')
                ->setParameter('categoryId', $categoryId)
                ->setParameter('domainId', $domainId)
                ->getQuery()->getScalarResult();

            $readySeoCategorySetup = [];

            foreach ($scalarData as $data) {
                $readySeoCategorySetup[] = $data['json'];
            }

            $this->readySeoCategorySetup[$domainId][$categoryId] = $readySeoCategorySetup;
        }

        return $this->readySeoCategorySetup[$domainId][$categoryId];
    }

    /**
     * @param int[] $categoryIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix[][]
     */
    public function getAllIndexedByCategoryId(array $categoryIds, DomainConfig $domainConfig): array
    {
        $allReadyCategorySeoMixes = array_fill_keys($categoryIds, []);
        $result = $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.category IN(:categories)')
            ->andWhere('rcsm.domainId = :domainId')
            ->andWhere('rcsm.showInCategory = true')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('rcsm.h1', $domainConfig->getLocale()), 'asc')
            ->setParameters([
                'categories' => $categoryIds,
                'domainId' => $domainConfig->getId(),
            ])
            ->getQuery()
            ->execute();

        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        foreach ($result as $readyCategorySeoMix) {
            $allReadyCategorySeoMixes[$readyCategorySeoMix->getCategory()->getId()][] = $readyCategorySeoMix;
        }

        return $allReadyCategorySeoMixes;
    }

    /**
     * @return array<int>
     */
    public function getAllCategoryIdsInSeoMixes(): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('identity(rcsm.category) as categoryId')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->distinct()
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'categoryId');
    }

    public function reset(): void
    {
        $this->readySeoCategorySetup = [];
    }
}
