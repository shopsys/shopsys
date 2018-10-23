<?php

namespace Shopsys\PohodaBundle\Component\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManager;

class PohodaEntityManager extends EntityManagerDecorator
{
    /**
     * Factory method to create EntityManager instances.
     *
     * @param mixed         $conn         an array with the connection parameters or an existing Connection instance
     * @param \Doctrine\ORM\Configuration $config       the Configuration instance to use
     * @param \Doctrine\Common\EventManager  $eventManager the EventManager instance to use
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @return \Shopsys\PohodaBundle\Component\Doctrine\PohodaEntityManager the created EntityManager
     */
    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        $test = new self(EntityManager::create($conn, $config, $eventManager));
        return $test;
    }
}
