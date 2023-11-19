<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Shopsys\FrameworkBundle\Model\Security\Roles;

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
    protected function getAdministratorRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Administrator::class);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findById($administratorId): ?\Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->getAdministratorRepository()->find($administratorId);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getById($administratorId): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $administrator = $this->getAdministratorRepository()->find($administratorId);

        if ($administrator === null) {
            $message = 'Administrator with ID ' . $administratorId . ' not found.';

            throw new AdministratorNotFoundException($message);
        }

        return $administrator;
    }

    /**
     * @param string $multidomainLoginToken
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByValidMultidomainLoginToken($multidomainLoginToken): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $queryBuilder = $this->getAdministratorRepository()
            ->createQueryBuilder('a')
            ->where('a.multidomainLoginToken = :multidomainLoginToken')
            ->setParameter('multidomainLoginToken', $multidomainLoginToken)
            ->andWhere('a.multidomainLoginTokenExpiration > CURRENT_TIMESTAMP()');
        $administrator = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($administrator === null) {
            $message = 'Administrator with valid multidomain login token ' . $multidomainLoginToken . ' not found.';

            throw new InvalidTokenException($message);
        }

        return $administrator;
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findByUserName($administratorUserName): ?\Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->getAdministratorRepository()->findOneBy(['username' => $administratorUserName]);
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByUserName($administratorUserName): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->join('a.roles', 'ar')
            ->where('ar.role = :role')
            ->setParameter('role', Roles::ROLE_ADMIN);
    }

    /**
     * @return int
     */
    public function getCountExcludingSuperadmin(): int
    {
        return (int)($this->getAllListableQueryBuilder()
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }
}
