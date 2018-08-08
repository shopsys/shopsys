<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

abstract class AbstractNativeFixture extends AbstractFixture
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function autowireEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array|null $parameters
     * @return mixed
     */
    protected function executeNativeQuery(string $sql, array $parameters = null)
    {
        $nativeQuery = $this->entityManager->createNativeQuery($sql, new ResultSetMapping());
        return $nativeQuery->execute($parameters);
    }
}
