<?php

declare(strict_types=1);

namespace App\Model\Payment\Grid;

use Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory as BasePaymentGridFactory;

/**
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Payment\PaymentRepository $paymentRepository, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \App\Model\Payment\PaymentFacade $paymentFacade, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade)
 * @method \Shopsys\FrameworkBundle\Component\Money\Money getDisplayPrice(\App\Model\Payment\Payment $payment)
 */
class PaymentGridFactory extends BasePaymentGridFactory
{
}
