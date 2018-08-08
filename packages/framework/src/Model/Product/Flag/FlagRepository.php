<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;

class FlagRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getFlagRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Flag::class);
    }

    public function findById(int $flagId): ?\Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        return $this->getFlagRepository()->find($flagId);
    }

    public function getById(int $flagId): \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
    {
        $flag = $this->findById($flagId);

        if ($flag === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException('Flag with ID ' . $flagId . ' not found.');
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
}
