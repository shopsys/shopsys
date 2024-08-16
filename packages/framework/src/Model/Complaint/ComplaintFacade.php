<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Complaint\Exception\ComplaintNotFoundException;

class ComplaintFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository $complaintRepository
     */
    public function __construct(
        protected readonly ComplaintRepository $complaintRepository,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getComplaintsQueryBuilder(): QueryBuilder
    {
        return $this->complaintRepository->getComplaintsQueryBuilder();
    }
}
