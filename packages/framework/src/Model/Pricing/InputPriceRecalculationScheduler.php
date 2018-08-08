<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class InputPriceRecalculationScheduler
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculator
     */
    private $inputPriceRecalculator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var bool
     */
    private $recalculateInputPricesWithoutVat;

    /**
     * @var bool
     */
    private $recalculateInputPricesWithVat;

    public function __construct(InputPriceRecalculator $inputPriceRecalculator, Setting $setting)
    {
        $this->inputPriceRecalculator = $inputPriceRecalculator;
        $this->setting = $setting;
    }

    public function scheduleSetInputPricesWithoutVat(): void
    {
        $this->recalculateInputPricesWithoutVat = true;
    }

    public function scheduleSetInputPricesWithVat(): void
    {
        $this->recalculateInputPricesWithVat = true;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
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
