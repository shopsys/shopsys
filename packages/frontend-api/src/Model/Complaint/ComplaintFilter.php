<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use DateTimeImmutable;

class ComplaintFilter
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]|null $statuses
     */
    public function __construct(
        protected ?DateTimeImmutable $createdAfter = null,
        protected ?DateTimeImmutable $createdBefore = null,
        protected ?array $statuses = null,
        protected ?string $search = null,
    ) {
    }

    public function getCreatedAfter(): ?DateTimeImmutable
    {
        return $this->createdAfter;
    }

    public function getCreatedBefore(): ?DateTimeImmutable
    {
        return $this->createdBefore;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus[]|null
     */
    public function getStatuses(): ?array
    {
        return $this->statuses;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }
}
