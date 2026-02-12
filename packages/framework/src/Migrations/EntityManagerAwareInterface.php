<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerAwareInterface
{
    public function setEntityManager(EntityManagerInterface $entityManager): void;
}
