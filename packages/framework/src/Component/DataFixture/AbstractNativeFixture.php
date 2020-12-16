<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @deprecated Class is obsolete and will be removed in the next major
 */
abstract class AbstractNativeFixture extends AbstractFixture
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @required
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function autowireEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $sql
     * @param array|null $parameters
     * @return mixed
     */
    protected function executeNativeQuery($sql, ?array $parameters = null)
    {
        @trigger_error(
            sprintf(
                'The "%s" class is deprecated and will be removed in the next major.',
                self::class
            ),
            E_USER_DEPRECATED
        );

        $nativeQuery = $this->entityManager->createNativeQuery($sql, new ResultSetMapping());
        return $nativeQuery->execute($parameters);
    }
}
