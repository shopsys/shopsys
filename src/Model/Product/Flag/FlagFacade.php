<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade as BaseFlagFacade;

/**
 * @property \App\Model\Product\Flag\FlagRepository $flagRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Flag\FlagRepository $flagRepository, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFactory $flagFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
 * @method \App\Model\Product\Flag\Flag getById(int $flagId)
 * @method \App\Model\Product\Flag\Flag create(\App\Model\Product\Flag\FlagData $flagData)
 * @method \App\Model\Product\Flag\Flag edit(int $flagId, \App\Model\Product\Flag\FlagData $flagData)
 * @method \App\Model\Product\Flag\Flag[] getAll()
 * @method dispatchFlagEvent(\App\Model\Product\Flag\Flag $flag, string $eventType)
 */
class FlagFacade extends BaseFlagFacade
{
    /**
     * @param string $akeneoCode
     * @return null|\App\Model\Product\Flag\Flag
     */
    public function findByAkeneoCode(string $akeneoCode): ?Flag
    {
        return $this->flagRepository->findByAkeneoCode($akeneoCode);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return array
     */
    public function getAllFlagAkeneoCodes(): array
    {
        return $this->flagRepository->getAllFlagAkeneoCodes();
    }

    /**
     * @param string $akeneoCode
     * @throws \RuntimeException
     * @return bool
     */
    public function deleteByAkeneoCode(string $akeneoCode): bool
    {
        $flag = $this->flagRepository->findByAkeneoCode($akeneoCode);
        if ($flag !== null) {
            $this->em->remove($flag);
            $this->em->flush();
            return true;
        }
        return false;
    }
}
