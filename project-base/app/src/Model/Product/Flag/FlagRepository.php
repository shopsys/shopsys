<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use App\Component\Doctrine\OrderByCollationHelper;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository as BaseFlagRepository;

/**
 * @method \App\Model\Product\Flag\Flag|null findById(int $flagId)
 * @method \App\Model\Product\Flag\Flag getById(int $flagId)
 * @method \App\Model\Product\Flag\Flag[] getAll()
 * @method \App\Model\Product\Flag\Flag[] getByIds(int[] $flagIds)
 * @method \App\Model\Product\Flag\Flag getByUuid(string $uuid)
 * @method \App\Model\Product\Flag\Flag[] getByUuids(string[] $uuids)
 */
class FlagRepository extends BaseFlagRepository
{
    /**
     * @param string $akeneoCode
     * @throws \RuntimeException
     * @return \App\Model\Product\Flag\Flag|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?Flag
    {
        return $this->getFlagRepository()->findOneBy(['akeneoCode' => $akeneoCode]);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return array
     */
    public function getAllFlagAkeneoCodes(): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('fl.akeneoCode')
            ->from(Flag::class, 'fl')
            ->getQuery()
            ->execute();

        return array_column($result, 'akeneoCode');
    }

    /**
     * @param int[] $flagsIds
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getVisibleFlagsByIds(array $flagsIds, string $locale): array
    {
        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f, ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.id IN (:flagsIds)')
            ->andWhere('f.visible = true')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('flagsIds', $flagsIds)
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $flagId
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag
     */
    public function getVisibleFlagById(int $flagId, string $locale): Flag
    {
        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f, ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.id = :flagId')
            ->andWhere('f.visible = true')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('flagId', $flagId)
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getAllVisibleFlags(string $locale): array
    {
        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f, ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.visible = true')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param string $uuid
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag
     */
    public function getVisibleByUuid(string $uuid, string $locale): Flag
    {
        $flagsQueryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f, ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('f.visible = true')
            ->andWhere('f.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        $flag = $flagsQueryBuilder->getQuery()->getOneOrNullResult();

        if ($flag === null) {
            throw new FlagNotFoundException(sprintf('Flag with UUID "%s" does not exist.', $uuid));
        }

        return $flag;
    }

    /**
     * @param int $flagId
     * @return \App\Model\Product\Flag\FlagDependenciesData
     */
    public function getFlagDependencies(int $flagId): FlagDependenciesData
    {
        $flagDependenciesData = new FlagDependenciesData();

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

        return $flagDependenciesData;
    }
}
