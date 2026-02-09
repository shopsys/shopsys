<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\EntityRepository;
use Override;
use Shopsys\FrameworkBundle\Model\NumberSequence\AbstractNumberSequenceRepository;

class ComplaintNumberSequenceRepository extends AbstractNumberSequenceRepository
{
    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequence>
     */
    #[Override]
    protected function getNumberSequenceRepository(): EntityRepository
    {
        return $this->em->getRepository(ComplaintNumberSequence::class);
    }
}
