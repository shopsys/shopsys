<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

class InquiryDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData
     */
    protected function createInstance(): InquiryData
    {
        return new InquiryData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData
     */
    public function create(): InquiryData
    {
        return $this->createInstance();
    }
}
