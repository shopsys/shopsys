<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

class GiftVoucherDataFactory
{
    protected function createInstance(): GiftVoucherData
    {
        return new GiftVoucherData();
    }

    public function create(): GiftVoucherData
    {
        return $this->createInstance();
    }

    public function createFromGiftVoucher(GiftVoucher $giftVoucher): GiftVoucherData
    {
        $giftVoucherData = $this->createInstance();
        $this->fillFromGiftVoucher($giftVoucherData, $giftVoucher);

        return $giftVoucherData;
    }

    protected function fillFromGiftVoucher(GiftVoucherData $giftVoucherData, GiftVoucher $giftVoucher): void
    {
        $giftVoucherData->code = $giftVoucher->getCode();
        $giftVoucherData->domainId = $giftVoucher->getDomainId();
        $giftVoucherData->valueWithVat = $giftVoucher->getValueWithVat();
        $giftVoucherData->currencyCode = $giftVoucher->getCurrencyCode();
        $giftVoucherData->vatPercent = $giftVoucher->getVatPercent();
        $giftVoucherData->status = $giftVoucher->getStatus();
        $giftVoucherData->activatedAt = $giftVoucher->getActivatedAt();
        $giftVoucherData->validUntil = $giftVoucher->getValidUntil();
        $giftVoucherData->productCatnum = $giftVoucher->getProductCatnum();
        $giftVoucherData->productName = $giftVoucher->getProductName();
        $giftVoucherData->customerEmail = $giftVoucher->getCustomerEmail();
        $giftVoucherData->createdOnOrder = $giftVoucher->getCreatedOnOrder();
    }
}
