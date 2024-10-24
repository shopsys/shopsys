<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
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

    /**
     * @param int[] $flagsIds
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getVisibleFlagsByIds(array $flagsIds, string $locale): array
    {
        $flagsQueryBuilder = $this->getVisibleQueryBuilder()
            ->addSelect('ft')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->where('f.id IN (:flagsIds)')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('flagsIds', $flagsIds)
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
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

    /**
     * @param int $flagId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
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
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getAllVisibleFlags(string $locale): array
    {
        $flagsQueryBuilder = $this->getVisibleQueryBuilder()
            ->addSelect('f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('locale', $locale);

        return $flagsQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param string $uuid
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
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
}
