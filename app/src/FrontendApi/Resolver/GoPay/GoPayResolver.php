<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\GoPay;

use App\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class GoPayResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade
     */
    private GoPayBankSwiftFacade $goPayBankSwiftFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private CurrencyFacade $currencyFacade;

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        GoPayBankSwiftFacade $goPayBankSwiftFacade,
        CurrencyFacade $currencyFacade
    ) {
        $this->goPayBankSwiftFacade = $goPayBankSwiftFacade;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param string $currencyCode
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function getGoPaySwifts(string $currencyCode): array
    {
        $currency = $this->currencyFacade->getByCode($currencyCode);

        return $this->goPayBankSwiftFacade->getAllByCurrencyId($currency->getId());
    }

    /**
     * @return array
     */
    public static function getAliases(): array
    {
        return [
            'getGoPaySwifts' => 'getGoPaySwifts',
        ];
    }
}
