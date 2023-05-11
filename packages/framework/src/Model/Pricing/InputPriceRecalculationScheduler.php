<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class InputPriceRecalculationScheduler
{
    protected InputPriceRecalculator $inputPriceRecalculator;

    protected Setting $setting;

    protected bool $recalculateInputPricesWithoutVat;

    protected bool $recalculateInputPricesWithVat;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculator $inputPriceRecalculator
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(InputPriceRecalculator $inputPriceRecalculator, Setting $setting)
    {
        $this->inputPriceRecalculator = $inputPriceRecalculator;
        $this->setting = $setting;
    }

    public function scheduleSetInputPricesWithoutVat()
    {
        $this->recalculateInputPricesWithoutVat = true;
    }

    public function scheduleSetInputPricesWithVat()
    {
        $this->recalculateInputPricesWithVat = true;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->recalculateInputPricesWithoutVat) {
            $this->inputPriceRecalculator->recalculateToInputPricesWithoutVat();
            $this->setting->set(
                PricingSetting::INPUT_PRICE_TYPE,
                PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT
            );
        } elseif ($this->recalculateInputPricesWithVat) {
            $this->inputPriceRecalculator->recalculateToInputPricesWithVat();
            $this->setting->set(
                PricingSetting::INPUT_PRICE_TYPE,
                PricingSetting::INPUT_PRICE_TYPE_WITH_VAT
            );
        }
    }
}
