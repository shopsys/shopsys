<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Shopsys\FrameworkBundle\Model\Security\Roles;

class AdministratorRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdministratorRepository(): EntityRepository
    {
        return $this->em->getRepository(Administrator::class);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findById(int $administratorId): ?Administrator
    {
        return $this->getAdministratorRepository()->find($administratorId);
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getById(int $administratorId): Administrator
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
    public function getByValidMultidomainLoginToken(string $multidomainLoginToken): Administrator
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
    public function findByUserName(string $administratorUserName): ?Administrator
    {
        return $this->getAdministratorRepository()->findOneBy(['username' => $administratorUserName]);
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByUserName(string $administratorUserName): Administrator
    {
        $administrator = $this->findByUserName($administratorUserName);
        if ($administrator === null) {
            throw new AdministratorNotFoundException(
                'Administrator with username "' . $administratorUserName . '" not found.'
            );
        }

        return $administrator;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableQueryBuilder(): QueryBuilder
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
