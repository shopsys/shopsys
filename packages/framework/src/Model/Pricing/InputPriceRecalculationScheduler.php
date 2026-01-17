<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class InputPriceRecalculationScheduler
{
    protected bool $recalculateInputPricesWithoutVat = false;

    protected bool $recalculateInputPricesWithVat = false;

    public function __construct(
        protected readonly InputPriceRecalculator $inputPriceRecalculator,
        protected readonly Setting $setting,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    public function scheduleSetInputPricesWithoutVat()
    {
        $this->recalculateInputPricesWithoutVat = true;
    }

    public function scheduleSetInputPricesWithVat()
    {
        $this->recalculateInputPricesWithVat = true;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->recalculateInputPricesWithoutVat) {
            $this->inputPriceRecalculator->recalculateToInputPricesWithoutVat();
            $this->setting->set(
                PricingSetting::INPUT_PRICE_TYPE,
                PricingSetting::PRICE_TYPE_WITHOUT_VAT,
            );
        } elseif ($this->recalculateInputPricesWithVat) {
            $this->inputPriceRecalculator->recalculateToInputPricesWithVat();
            $this->setting->set(
                PricingSetting::INPUT_PRICE_TYPE,
                PricingSetting::PRICE_TYPE_WITH_VAT,
            );
        }

        if ($this->recalculateInputPricesWithVat || $this->recalculateInputPricesWithoutVat) {
            $this->productRecalculationDispatcher->dispatchAllProducts([ProductExportScopeConfig::SCOPE_PRICE]);
        }
    }
}
