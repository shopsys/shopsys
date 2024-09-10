<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequenceRepository;

class ComplaintNumberSequenceRepository extends AbstractNumberSequenceRepository
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getNumberSequenceRepository(): EntityRepository
    {
        return $this->em->getRepository(ComplaintNumberSequence::class);
    }
}
