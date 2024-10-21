<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository as BaseFlagRepository;

/**
 * @method \App\Model\Product\Flag\Flag|null findById(int $flagId)
 * @method \App\Model\Product\Flag\Flag getById(int $flagId)
 * @method \App\Model\Product\Flag\Flag[] getAll()
 * @method \App\Model\Product\Flag\Flag[] getByIds(int[] $flagIds)
 * @method \App\Model\Product\Flag\Flag getByUuid(string $uuid)
 * @method \App\Model\Product\Flag\Flag[] getByUuids(string[] $uuids)
 * @method \App\Model\Product\Flag\Flag[] getVisibleFlagsByIds(int[] $flagsIds, string $locale)
 * @method \App\Model\Product\Flag\Flag getVisibleFlagById(int $flagId, string $locale)
 */
class FlagRepository extends BaseFlagRepository
{
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
