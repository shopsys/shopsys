<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository as BasePaymentRepository;

/**
 * @method \App\Model\Payment\Payment[] getAll()
 * @method \App\Model\Payment\Payment[] getAllIncludingDeleted()
 * @method \App\Model\Payment\Payment|null findById(int $id)
 * @method \App\Model\Payment\Payment getById(int $id)
 * @method \App\Model\Payment\Payment[] getAllByTransport(\App\Model\Transport\Transport $transport)
 * @method \App\Model\Payment\Payment getOneByUuid(string $uuid)
 * @method \App\Model\Payment\Payment[] getByGoPayPaymentMethod(\Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod, int $domainId)
 * @method \App\Model\Payment\Payment getEnabledOnDomainByUuid(string $uuid, int $domainId)
 * @method \App\Model\Payment\Payment[] getAllWithEagerLoadedDomainsAndTranslations(\Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 */
class PaymentRepository extends BasePaymentRepository
{
}
