<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

class ComplaintStatusData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    public function __construct()
    {
        $this->name = [];
    }
}
