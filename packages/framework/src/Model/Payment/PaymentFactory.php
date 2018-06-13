<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface
     */
    protected $paymentPriceFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface $paymentPriceFactory
     */
    public function __construct(CurrencyFacade $currencyFacade, PaymentPriceFactoryInterface $paymentPriceFactory)
    {
        $this->currencyFacade = $currencyFacade;
        $this->paymentPriceFactory = $paymentPriceFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $data
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function create(PaymentData $data): Payment
    {
        return new Payment($data, $this->currencyFacade->getAll(), $this->paymentPriceFactory);
    }
}
