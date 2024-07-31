<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

class ComplaintItemDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    public function create(): ComplaintItemData
    {
        return $this->createInstance();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    protected function createInstance(): ComplaintItemData
    {
        return new ComplaintItemData();
    }
}
