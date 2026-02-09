<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayOrderStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PaymentTransactionExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('translate_payment_transaction_status', GoPayOrderStatus::getTranslatedGoPayStatus(...)),
            new TwigFilter('translate_payment_transaction_sub_status', GoPayOrderStatus::getTranslatedGoPaySubStatus(...)),
        ];
    }
}
