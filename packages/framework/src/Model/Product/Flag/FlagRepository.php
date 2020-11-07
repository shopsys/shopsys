<?php

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagNotFoundException;

class FlagRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

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
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAll()
    {
        return $this->getFlagRepository()->findBy([], ['id' => 'asc']);
    }

    /**
     * @param int[] $flagsIds
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlagsForFilterByIds(array $flagsIds, string $locale): array
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
