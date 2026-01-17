<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;

class FlagRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
        protected readonly FlagDependenciesDataFactory $flagDependenciesDataFactory,
    ) {
    }

    protected function getFlagRepository(): EntityRepository
    {
        return $this->em->getRepository(Flag::class);
    }

    public function findById(int $flagId): ?Flag
    {
        return $this->getFlagRepository()->find($flagId);
    }

    public function getById(int $flagId): Flag
    {
        $flag = $this->findById($flagId);

        if ($flag === null) {
            throw new FlagNotFoundException('Flag with ID ' . $flagId . ' not found.');
        }

        return $flag;
    }

    /**
     * @param int[] $flagIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getByIds(array $flagIds): array
    {
        return $this->getFlagRepository()->findBy(['id' => $flagIds], ['id' => 'asc']);
    }

    public function getByUuid(string $uuid): Flag
    {
        $flag = $this->getFlagRepository()->findOneBy(['uuid' => $uuid]);

        if ($flag === null) {
            throw new FlagNotFoundException('Flag with UUID ' . $uuid . ' does not exist.');
        }

        return $flag;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAll(): array
    {
        return $this->getFlagRepository()->findBy([], ['id' => 'asc']);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getByUuids(array $uuids): array
    {
        return $this->getFlagRepository()->findBy(['uuid' => $uuids]);
    }

    /**
     * @param int[] $flagsIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getVisibleFlagsByIds(array $flagsIds, string $locale): array
    {
        $flagsQueryBuilder = $this->getVisibleQueryBuilder()
            ->addSelect('ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.id IN (:flagsIds)')
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('flagsIds', $flagsIds)
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }

    public function getVisibleQueryBuilder(): QueryBuilder
    {
        return $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f')
            ->where('f.visible = true');
    }

    /**
     * @param string[] $flagUuids
     * @return int[]
     */
    public function getFlagIdsByUuids(array $flagUuids): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('f.id')
            ->from(Flag::class, 'f')
            ->where('f.uuid IN (:uuids)')
            ->setParameter('uuids', $flagUuids);

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'id');
    }

    public function getVisibleFlagById(int $flagId, string $locale): Flag
    {
        $flagsQueryBuilder = $this->getVisibleQueryBuilder()
            ->addSelect('ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.id = :flagId')
            ->setParameter('flagId', $flagId)
            ->setParameter('locale', $locale);

        $flag = $flagsQueryBuilder->getQuery()->getOneOrNullResult();

        if ($flag === null) {
            throw new FlagNotFoundException(sprintf('Flag with ID "%s" does not exist.', $flagId));
        }

        return $flag;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAllVisibleFlags(string $locale): array
    {
        $flagsQueryBuilder = $this->getVisibleQueryBuilder()
            ->addSelect('f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }

    public function getVisibleByUuid(string $uuid, string $locale): Flag
    {
        $flagsQueryBuilder = $this->getVisibleQueryBuilder()
            ->addSelect('ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('f.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        $flag = $flagsQueryBuilder->getQuery()->getOneOrNullResult();

        if ($flag === null) {
            throw new FlagNotFoundException(sprintf('Flag with UUID "%s" does not exist.', $uuid));
        }

        return $flag;
    }

    public function getFlagDependencies(int $flagId): FlagDependenciesData
    {
        $flagDependenciesData = $this->flagDependenciesDataFactory->create();

        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('1')
            ->join(PromoCodeFlag::class, 'pcf', Join::WITH, 'pcf.flag = f')
            ->groupBy('f.id')
            ->andWhere('f.id = :flagId')
            ->setParameter('flagId', $flagId);
        $flagDependenciesData->hasPromoCodeDependency = (bool)$flagsQueryBuilder->getQuery()->getOneOrNullResult();

        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('1')
            ->join(ReadyCategorySeoMix::class, 'rcsm', Join::WITH, 'rcsm.flag = f')
            ->groupBy('f.id')
            ->andWhere('f.id = :flagId')
            ->setParameter('flagId', $flagId);
        $flagDependenciesData->hasSeoMixDependency = (bool)$flagsQueryBuilder->getQuery()->getOneOrNullResult();

        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('1')
            ->join(ProductDomain::class, 'pd', Join::WITH, 'pd.promotionXy = f.promotionXy')
            ->andWhere('f.id = :flagId')
            ->andWhere('pd.promotionXy IS NOT NULL')
            ->setParameter('flagId', $flagId)
            ->groupBy('f.id');
        $flagDependenciesData->hasPromotionXyDependency = (bool)$flagsQueryBuilder->getQuery()->getOneOrNullResult();

        return $flagDependenciesData;
    }

    /**
     * @return int[]
     */
    public function getFlagsIdsWithPromotionXy(): array
    {
        $queryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f.id')
            ->where('f.promotionXy IS NOT NULL');

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'id');
    }
}
