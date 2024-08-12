<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequenceRepository;

class OrderNumberSequenceRepository extends AbstractNumberSequenceRepository
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getNumberSequenceRepository(): EntityRepository
    {
        return $this->em->getRepository(OrderNumberSequence::class);
    }
}
