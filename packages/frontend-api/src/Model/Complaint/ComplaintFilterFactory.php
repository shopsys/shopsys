<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;

class ComplaintFilterFactory
{
    public function __construct(
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
    ) {
    }

    public function createFromArgument(Argument $argument): ComplaintFilter
    {
        if (!isset($argument['filter'])) {
            return new ComplaintFilter();
        }

        $filter = $argument['filter'];
        $statusCodes = $filter['statusCodes'] ?? [];
        $statuses = $this->complaintStatusFacade->getAllByCodes($statusCodes);

        $search = isset($filter['search']) ? trim($filter['search']) : null;

        return new ComplaintFilter(
            $filter['createdAfter'] ?? null,
            $filter['createdBefore'] ?? null,
            $statuses !== [] ? $statuses : null,
            $search !== '' ? $search : null,
        );
    }
}
