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
     * @var bool
     */
    public $closed;

    public function __construct()
    {
        $this->name = [];
        $this->closed = false;
    }
}
