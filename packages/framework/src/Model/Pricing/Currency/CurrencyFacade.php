<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\EntityManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPrice;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Transport\TransportPrice;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

class CurrencyFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyRepository
     */
    private $currencyRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyService
     */
    private $currencyService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    private $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportRepository
     */
    private $transportRepository;

    public function __construct(
        EntityManager $em,
        CurrencyRepository $currencyRepository,
        CurrencyService $currencyService,
        PricingSetting $pricingSetting,
        OrderRepository $orderRepository,
        Domain $domain,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        PaymentRepository $paymentRepository,
        TransportRepository $transportRepository
    ) {
        $this->em = $em;
        $this->currencyRepository = $currencyRepository;
        $this->currencyService = $currencyService;
        $this->pricingSetting = $pricingSetting;
        $this->orderRepository = $orderRepository;
        $this->domain = $domain;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->paymentRepository = $paymentRepository;
        $this->transportRepository = $transportRepository;
    }

    /**
     * @param int $currencyId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getById($currencyId)
    {
        return $this->currencyRepository->getById($currencyId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function create(CurrencyData $currencyData)
    {
        $currency = $this->currencyService->create($currencyData);
        $this->em->persist($currency);
        $this->em->flush($currency);
        $this->createTransportAndPaymentPrices($currency);

        return $currency;
    }

    /**
     * @param int $currencyId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function edit($currencyId, CurrencyData $currencyData)
    {
        $currency = $this->currencyRepository->getById($currencyId);
        $this->currencyService->edit($currency, $currencyData, $this->isDefaultCurrency($currency));
        $this->em->flush();
        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();

        return $currency;
    }

    /**
     * @param int $currencyId
     */
    public function deleteById($currencyId)
    {
        $currency = $this->currencyRepository->getById($currencyId);

        if (in_array($currency->getId(), $this->getNotAllowedToDeleteCurrencyIds(), true)) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException();
        }
        $this->em->remove($currency);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getAll()
    {
        return $this->currencyRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getDefaultCurrency()
    {
        return $this->getById($this->pricingSetting->getDefaultCurrencyId());
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getDomainDefaultCurrencyByDomainId($domainId)
    {
        return $this->getById($this->pricingSetting->getDomainDefaultCurrencyIdByDomainId($domainId));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    public function setDefaultCurrency(Currency $currency)
    {
        $this->pricingSetting->setDefaultCurrency($currency);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     */
    public function setDomainDefaultCurrency(Currency $currency, $domainId)
    {
        $this->pricingSetting->setDomainDefaultCurrency($currency, $domainId);
        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();
    }

    /**
     * @return int[]
     */
    public function getNotAllowedToDeleteCurrencyIds()
    {
        return $this->currencyService->getNotAllowedToDeleteCurrencyIds(
            $this->getDefaultCurrency()->getId(),
            $this->getCurrenciesUsedInOrders(),
            $this->pricingSetting,
            $this->domain
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return bool
     */
    public function isDefaultCurrency(Currency $currency)
    {
        return $currency === $this->getDefaultCurrency();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getCurrenciesUsedInOrders()
    {
        return $this->orderRepository->getCurrenciesUsedInOrders();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getAllIndexedById()
    {
        $currenciesIndexedById = [];
        foreach ($this->getAll() as $currency) {
            $currenciesIndexedById[$currency->getId()] = $currency;
        }

        return $currenciesIndexedById;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    private function createTransportAndPaymentPrices(Currency $currency)
    {
        $toFlush = [];
        foreach ($this->paymentRepository->getAll() as $payment) {
            $paymentPrice = new PaymentPrice($payment, $currency, 0);
            $this->em->persist($paymentPrice);
            $toFlush[] = $paymentPrice;
        }
        foreach ($this->transportRepository->getAll() as $transport) {
            $transportPrice = new TransportPrice($transport, $currency, 0);
            $this->em->persist($transportPrice);
            $toFlush[] = $transportPrice;
        }

        $this->em->flush($toFlush);
    }
}
