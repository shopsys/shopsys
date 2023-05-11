<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;

class FlagRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFlagRepository()
    {
        return $this->em->getRepository(Flag::class);
    }

    /**
     * @param int $flagId
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|null
     */
    public function findById($flagId)
    {
        return $this->getFlagRepository()->find($flagId);
    }

    /**
     * @param int $flagId
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function getById($flagId)
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

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
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
    public function getAll()
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
}
