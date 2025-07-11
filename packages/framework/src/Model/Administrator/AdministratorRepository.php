<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;

class AdministratorRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdministratorRepository()
    {
        return $this->em->getRepository(Administrator::class);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findById($administratorId)
    {
        return $this->getAdministratorRepository()->find($administratorId);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getById($administratorId)
    {
        $administrator = $this->getAdministratorRepository()->find($administratorId);

        if ($administrator === null) {
            $message = 'Administrator with ID ' . $administratorId . ' not found.';

            throw new AdministratorNotFoundException($message);
        }

        return $administrator;
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findByUserName($administratorUserName)
    {
        return $this->getAdministratorRepository()->findOneBy(['username' => $administratorUserName]);
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findByUserNameWithPasswordFilled(string $administratorUserName): ?Administrator
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->where('a.username = :username')
            ->andWhere('a.password is not NULL')
            ->setParameter('username', $administratorUserName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByUserName($administratorUserName)
    {
        $administrator = $this->findByUserName($administratorUserName);

        if ($administrator === null) {
            throw new AdministratorNotFoundException(
                'Administrator with username "' . $administratorUserName . '" not found.',
            );
        }

        return $administrator;
    }

    /**
     * @param string $administratorEmail
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByEmail(string $administratorEmail): Administrator
    {
        $administrator = $this->getAdministratorRepository()->findOneBy(['email' => $administratorEmail]);

        if ($administrator === null) {
            throw new AdministratorNotFoundException(
                'Administrator with email "' . $administratorEmail . '" not found.',
            );
        }

        return $administrator;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableQueryBuilder()
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->leftJoin('a.roles', 'ar')
            ->where('ar.role = :role')
            ->orWhere('a.roleGroup is not NULL')
            ->setParameter('role', SystemRole::ADMIN);
    }

    /**
     * @return int
     */
    public function getCountExcludingSuperadmin()
    {
        return (int)($this->getAllListableQueryBuilder()
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int $roleGroupId
     * @return string[]
     */
    public function findAdministratorNamesWithRoleGroup(int $roleGroupId): array
    {
        $administrators = $this->getAdministratorRepository()
            ->createQueryBuilder('a')
            ->select('a.realName')
            ->where('a.roleGroup = :roleGroupId')
            ->setParameter('roleGroupId', $roleGroupId)
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            return $item['realName'];
        }, $administrators);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findByUuid(string $uuid): ?Administrator
    {
        return $this->getAdministratorRepository()->findOneBy(['uuid' => $uuid]);
    }
}
