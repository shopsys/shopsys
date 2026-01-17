<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

class InquiryDataFactory
{
    protected function createInstance(): InquiryData
    {
        return new InquiryData();
    }

    public function create(int $domainId): InquiryData
    {
        $inquiryData = $this->createInstance();
        $inquiryData->domainId = $domainId;

        return $inquiryData;
    }
}
