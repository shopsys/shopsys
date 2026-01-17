<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\GoPay;

use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class GoPayQuery extends AbstractQuery
{
    public function __construct(
        protected readonly GoPayBankSwiftFacade $goPayBankSwiftFacade,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function goPaySwiftsQuery(string $currencyCode): array
    {
        $currency = $this->currencyFacade->getByCode($currencyCode);

        return $this->goPayBankSwiftFacade->getAllByCurrencyId($currency->getId());
    }
}
