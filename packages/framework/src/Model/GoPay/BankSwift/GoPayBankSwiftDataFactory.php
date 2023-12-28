<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

class GoPayBankSwiftDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftData
     */
    public function createInstance(): GoPayBankSwiftData
    {
        return new GoPayBankSwiftData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift $goPayBankSwift
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftData
     */
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
