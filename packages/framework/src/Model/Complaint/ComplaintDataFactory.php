<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

class ComplaintDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    public function create(): ComplaintData
    {
        return $this->createInstance();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData
     */
    protected function createInstance(): ComplaintData
    {
        return new ComplaintData();
    }
}
