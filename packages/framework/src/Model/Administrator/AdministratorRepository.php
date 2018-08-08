<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;

class AdministratorRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getAdministratorRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Administrator::class);
    }

    /**
     * @param int $administratorId
     */
    public function findById($administratorId): ?\Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->getAdministratorRepository()->find($administratorId);
    }

    /**
     * @param int $administratorId
     */
    public function getById($administratorId): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $administrator = $this->getAdministratorRepository()->find($administratorId);
        if ($administrator === null) {
            $message = 'Administrator with ID ' . $administratorId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException($message);
        }

        return $administrator;
    }

    /**
     * @param string $multidomainLoginToken
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
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException($message);
        }

        return $administrator;
    }

    /**
     * @param string $administratorUserName
     */
    public function findByUserName($administratorUserName): ?\Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->getAdministratorRepository()->findOneBy(['username' => $administratorUserName]);
    }

    /**
     * @param string $administratorUserName
     */
    public function getByUserName($administratorUserName): \Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        $administrator = $this->findByUserName($administratorUserName);
        if ($administrator === null) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorNotFoundException(
                'Administrator with username "' . $administratorUserName . '" not found.'
            );
        }

        return $administrator;
    }

    public function getAllListableQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->getAdministratorRepository()
            ->createQueryBuilder('a')
            ->where('a.superadmin = :isSuperadmin')
            ->setParameter('isSuperadmin', false);
    }

    public function getCountExcludingSuperadmin(): int
    {
        return (int)($this->getAllListableQueryBuilder()
            ->select('COUNT(a)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int $id
     * @param string $loginToken
     */
    public function findByIdAndLoginToken($id, $loginToken): ?\Shopsys\FrameworkBundle\Model\Administrator\Administrator
    {
        return $this->getAdministratorRepository()->findOneBy([
            'id' => $id,
            'loginToken' => $loginToken,
        ]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator[]
     */
    public function getAllSuperadmins(): array
    {
        return $this->getAdministratorRepository()->findBy(['superadmin' => true]);
    }
}
