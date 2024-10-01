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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData
     */
    public function create(int $domainId): InquiryData
    {
        $inquiryData = $this->createInstance();
        $inquiryData->domainId = $domainId;

        return $inquiryData;
    }
}
