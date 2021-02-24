<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository as BaseFlagRepository;

/**
 * @method \App\Model\Product\Flag\Flag|null findById(int $flagId)
 * @method \App\Model\Product\Flag\Flag getById(int $flagId)
 * @method \App\Model\Product\Flag\Flag[] getAll()
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
}
