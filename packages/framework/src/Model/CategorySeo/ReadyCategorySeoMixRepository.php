<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ObjectRepository;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;

class ReadyCategorySeoMixRepository
{
    protected const string READY_SEO_CATEGORY_SETUP_CACHE_NAMESPACE = 'readySeoCategorySetup';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getRepository(): ObjectRepository
    {
        return $this->em->getRepository(ReadyCategorySeoMix::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ChoseCategorySeoMixCombination $choseCategorySeoMixCombination
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
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
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findById(int $id): ?ReadyCategorySeoMix
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null
     */
    public function findByUuid(string $uuid): ?ReadyCategorySeoMix
    {
        return $this->getRepository()->findOneBy([
            'uuid' => $uuid,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix[]
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
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix
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

        $combinationJson = json_encode($combinationArray, JSON_THROW_ON_ERROR);

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
    protected function isJsonCombinationSeoCategory(int $categoryId, int $domainId, string $combinationJson): bool
    {
        $readySeoCategorySetup = $this->getReadySeoCategorySetupFromCache($categoryId, $domainId);

        return in_array($combinationJson, $readySeoCategorySetup, true);
    }

    /**
     * @param int $categoryId
     * @param int $domainId
     * @return string[]
     */
    protected function getReadySeoCategorySetupFromCache(int $categoryId, int $domainId): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            self::READY_SEO_CATEGORY_SETUP_CACHE_NAMESPACE,
            function () use ($categoryId, $domainId): array {
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

                return $readySeoCategorySetup;
            },
            $categoryId,
            $domainId,
        );
    }

    /**
     * @param int[] $categoryIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix[][]
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

        /** @var \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
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
}
