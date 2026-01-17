<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

class GoPayBankSwiftDataFactory
{
    public function createInstance(): GoPayBankSwiftData
    {
        return new GoPayBankSwiftData();
    }

    public function createFromGoPayBankSwift(GoPayBankSwift $goPayBankSwift): GoPayBankSwiftData
    {
        $goPayBankSwiftData = $this->createInstance();

        $goPayBankSwiftData->swift = $goPayBankSwift->getSwift();
        $goPayBankSwiftData->goPayPaymentMethod = $goPayBankSwift->getGoPayPaymentMethod();
        $goPayBankSwiftData->name = $goPayBankSwift->getName();
        $goPayBankSwiftData->imageNormalUrl = $goPayBankSwift->getImageNormalUrl();
        $goPayBankSwiftData->imageLargeUrl = $goPayBankSwift->getImageLargeUrl();
        $goPayBankSwiftData->isOnline = $goPayBankSwift->isOnline();

        return $goPayBankSwiftData;
    }
}
