<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\Category\Category;
use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use function GuzzleHttp\json_encode;

class ReadyCategorySeoMixRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private Localization $localization;

    /**
     * @var string[][][]
     */
    private array $readySeoCategorySetup;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        EntityManagerInterface $em,
        Domain $domain,
        Localization $localization
    ) {
        $this->em = $em;
        $this->domain = $domain;
        $this->localization = $localization;
        $this->readySeoCategorySetup = [];
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

        $combinationJson = json_encode($combinationArray);

        if ($this->isJsonCombinationSeoCategory($categoryId, $domainConfig->getId(), $combinationJson) === false) {
            throw new UnableToFindReadyCategorySeoMixException(
                'Unable to find ReadyCategorySeoMix: no exact match by product filter form and ordering'
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
                'Unable to find ReadyCategorySeoMix: no exact match by product filter form and ordering'
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
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @return \App\Model\CategorySeo\ReadyCategorySeoMix[]
     */
    public function getAllForShowInCategory(Category $category, int $domainId): array
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
        $collation = $this->localization->getCollationByLocale($locale);

        return $this->em->createQueryBuilder()
            ->select('rcsm')
            ->from(ReadyCategorySeoMix::class, 'rcsm')
            ->andWhere('rcsm.category = :category')
            ->andWhere('rcsm.domainId = :domainId')
            ->andWhere('rcsm.showInCategory = true')
            ->orderBy("COLLATE(rcsm.h1, '" . $collation . "')", 'asc')
            ->setParameters([
                'category' => $category,
                'domainId' => $domainId,
            ])
            ->getQuery()
            ->execute();
    }
}
