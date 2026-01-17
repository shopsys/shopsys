<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository as BasePaymentRepository;

/**
 * @method \App\Model\Payment\Payment[] getAll()
 * @method \App\Model\Payment\Payment[] getAllIncludingDeleted()
 * @method \App\Model\Payment\Payment|null findById(int $id)
 * @method \App\Model\Payment\Payment getById(int $id)
 * @method \App\Model\Payment\Payment[] getAllByTransport( $transport)
 * @method \App\Model\Payment\Payment getOneByUuid(string $uuid)
 * @method \App\Model\Payment\Payment[] getByGoPayPaymentMethod( $goPayPaymentMethod,  $domainId)
 * @method \App\Model\Payment\Payment getEnabledOnDomainByUuid(string $uuid, int $domainId)
 * @method \App\Model\Payment\Payment[] getAllWithEagerLoadedDomainsAndTranslations( $domainConfig)
 * @method \App\Model\Payment\Payment|null findPaymentByExternalMethodTransportAndDomainId(string $externalPaymentMethod, \App\Model\Transport\Transport $transport, int $domainId)
 */
class PaymentRepository extends BasePaymentRepository
{
}
