<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

class ComplaintStatusData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string
     */
    public $statusType;

    public function __construct()
    {
        $this->name = [];
        $this->statusType = ComplaintStatusTypeEnum::STATUS_TYPE_IN_PROGRESS;
    }
}
