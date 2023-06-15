<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\GoPay;

use App\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class GoPayQuery extends AbstractQuery
{
    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        private readonly GoPayBankSwiftFacade $goPayBankSwiftFacade,
        private readonly CurrencyFacade $currencyFacade
    ) {
    }

    /**
     * @param string $currencyCode
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function goPaySwiftsQuery(string $currencyCode): array
    {
        $currency = $this->currencyFacade->getByCode($currencyCode);

        return $this->goPayBankSwiftFacade->getAllByCurrencyId($currency->getId());
    }
}
