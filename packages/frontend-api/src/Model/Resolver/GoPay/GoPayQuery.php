<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\GoPay;

use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class GoPayQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        protected readonly GoPayBankSwiftFacade $goPayBankSwiftFacade,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @param string $currencyCode
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function goPaySwiftsQuery(string $currencyCode): array
    {
        $currency = $this->currencyFacade->getByCode($currencyCode);

        return $this->goPayBankSwiftFacade->getAllByCurrencyId($currency->getId());
    }
}
