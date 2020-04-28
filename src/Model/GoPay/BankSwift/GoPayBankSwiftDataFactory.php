<?php

declare(strict_types = 1);

namespace App\Model\GoPay\BankSwift;

class GoPayBankSwiftDataFactory
{
    /**
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwiftData
     */
    public function create(): GoPayBankSwiftData
    {
        return new GoPayBankSwiftData();
    }

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwift $goPayBankSwift
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwiftData
     */
    public function createFromGoPayBankSwift(GoPayBankSwift $goPayBankSwift): GoPayBankSwiftData
    {
        $goPayBankSwiftData = $this->create();

        $goPayBankSwiftData->swift = $goPayBankSwift->getSwift();
        $goPayBankSwiftData->goPayPaymentMethod = $goPayBankSwift->getGoPayPaymentMethod();
        $goPayBankSwiftData->name = $goPayBankSwift->getName();
        $goPayBankSwiftData->imageNormalUrl = $goPayBankSwift->getImageNormalUrl();
        $goPayBankSwiftData->imageLargeUrl = $goPayBankSwift->getImageLargeUrl();
        $goPayBankSwiftData->isOnline = $goPayBankSwift->isOnline();

        return $goPayBankSwiftData;
    }
}
