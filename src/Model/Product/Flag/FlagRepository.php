<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Query\Expr\Join;
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

        return array_map('reset', $result);
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
            ->orderBy('ft.name', 'asc')
            ->setParameter('flagsIds', $flagsIds)
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }
}
