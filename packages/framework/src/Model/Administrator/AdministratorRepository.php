<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRole;

class AdministratorRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getAdministratorRepository(): EntityRepository
    {
        return $this->em->getRepository(Administrator::class);
    }

    public function findById(int $administratorId): ?Administrator
    {
        return $this->getAdministratorRepository()->find($administratorId);
    }

    public function getById(int $administratorId): Administrator
    {
        $administrator = $this->getAdministratorRepository()->find($administratorId);

        if ($administrator === null) {
            $message = 'Administrator with ID ' . $administratorId . ' not found.';

            throw new AdministratorNotFoundException($message);
        }

        return $administrator;
    }

    public function findByUserName(
        string $administratorUserName,
    ): ?Administrator {
        return $this->getAdministratorRepository()->findOneBy(['username' => $administratorUserName]);
    }

    public function findByUserNameWithPasswordFilled(string $administratorUserName): ?Administrator
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->where('a.username = :username')
            ->andWhere('a.password is not NULL')
            ->setParameter('username', $administratorUserName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByUserName(
        string $administratorUserName,
    ): Administrator {
        $administrator = $this->findByUserName($administratorUserName);

        if ($administrator === null) {
            throw new AdministratorNotFoundException(
                'Administrator with username "' . $administratorUserName . '" not found.',
            );
        }

        return $administrator;
    }

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

    public function getAllListableExcludingSuperadminQueryBuilder(): QueryBuilder
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->leftJoin('a.roles', 'ar')
            ->where('ar.role = :role OR a.roleGroup is not NULL')
            ->andWhere('ar.role != :superadminRole OR ar.role IS NULL')
            ->setParameter('role', SystemRole::ADMIN)
            ->setParameter('superadminRole', SystemRole::SUPER_ADMIN);
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        $subquery = $this->em->createQueryBuilder()
            ->select('1')
            ->from(AdministratorRole::class, 'ar')
            ->where('ar.administrator = a')
            ->andWhere('ar.role = :superadminRole')
            ->getDQL();

        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->addSelect(sprintf('CASE WHEN EXISTS(%s) THEN true ELSE false END AS is_superadmin', $subquery))
            ->setParameter('superadminRole', SystemRole::SUPER_ADMIN);
    }

    public function getCountExcludingSuperadmin(): int
    {
        return (int)($this->getAllListableExcludingSuperadminQueryBuilder()
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
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

    public function findByUuid(string $uuid): ?Administrator
    {
        return $this->getAdministratorRepository()->findOneBy(['uuid' => $uuid]);
    }
}
