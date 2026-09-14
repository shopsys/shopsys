<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use GoPay\Definition\Response\PaymentStatus;
use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayOrderStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PaymentTransactionExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('translate_payment_transaction_status', ExtendedClassNameResolver::resolve(GoPayOrderStatus::class)::getTranslatedGoPayStatus(...)),
            new TwigFilter('translate_payment_transaction_sub_status', ExtendedClassNameResolver::resolve(GoPayOrderStatus::class)::getTranslatedGoPaySubStatus(...)),
            new TwigFilter('gopay_payment_status_badge_class', $this->getBadgeClass(...)),
        ];
    }

    protected function getBadgeClass(?string $goPayStatus): string
    {
        return match ($goPayStatus) {
            PaymentStatus::PAID, PaymentStatus::AUTHORIZED, PaymentStatus::REFUNDED, PaymentStatus::PARTIALLY_REFUNDED => 'bg-green-lt',
            PaymentStatus::CANCELED, PaymentStatus::TIMEOUTED => 'bg-red-lt',
            default => 'bg-yellow-lt',
        };
    }
}
