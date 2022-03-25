<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Doctrine\ORM\EntityManagerInterface;
use Litipk\BigNumbers\Decimal;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

class CurrencyFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    protected $pricingSetting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    protected $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportRepository
     */
    protected $transportRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface
     */
    protected $paymentPriceFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface
     */
    protected $transportPriceFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFactoryInterface
     */
    protected $currencyFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyRepository $currencyRepository
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository $paymentRepository
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface $paymentPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactoryInterface $transportPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFactoryInterface $currencyFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CurrencyRepository $currencyRepository,
        PricingSetting $pricingSetting,
        OrderRepository $orderRepository,
        Domain $domain,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        PaymentRepository $paymentRepository,
        TransportRepository $transportRepository,
        PaymentPriceFactoryInterface $paymentPriceFactory,
        TransportPriceFactoryInterface $transportPriceFactory,
        CurrencyFactoryInterface $currencyFactory,
        VatFacade $vatFacade
    ) {
        $this->em = $em;
        $this->currencyRepository = $currencyRepository;
        $this->pricingSetting = $pricingSetting;
        $this->orderRepository = $orderRepository;
        $this->domain = $domain;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->paymentRepository = $paymentRepository;
        $this->transportRepository = $transportRepository;
        $this->paymentPriceFactory = $paymentPriceFactory;
        $this->transportPriceFactory = $transportPriceFactory;
        $this->currencyFactory = $currencyFactory;
        $this->vatFacade = $vatFacade;
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
     * @param string $currencyCode
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getByCode(string $currencyCode): Currency
    {
        return $this->currencyRepository->getByCode($currencyCode);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData $currencyData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function create(CurrencyData $currencyData)
    {
        $currency = $this->currencyFactory->create($currencyData);
        $this->em->persist($currency);
        $this->em->flush();

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
        $currency->edit($currencyData);
        if ($this->isDefaultCurrency($currency)) {
            $currency->setExchangeRate(Currency::DEFAULT_EXCHANGE_RATE);
        } else {
            $currency->setExchangeRate($currencyData->exchangeRate);
        }
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
            throw new DeletingNotAllowedToDeleteCurrencyException();
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
        $originalDefaultCurrency = $this->getDefaultCurrency();
        $this->pricingSetting->setDefaultCurrency($currency);
        $this->recalculateExchangeRatesByNewDefaultCurrency($originalDefaultCurrency, $currency);
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
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $originalDefaultCurrency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $newDefaultCurrency
     */
    protected function recalculateExchangeRatesByNewDefaultCurrency(
        Currency $originalDefaultCurrency,
        Currency $newDefaultCurrency
    ): void {
        $coefficient = $this->getExchangeRateForCurrencies($originalDefaultCurrency, $newDefaultCurrency);
        foreach ($this->getAll() as $currency) {
            if ($currency->getId() === $newDefaultCurrency->getId()) {
                $newExchangeRate = Currency::DEFAULT_EXCHANGE_RATE;
            } else {
                $newExchangeRate = Decimal::fromString($currency->getExchangeRate())->mul($coefficient);
            }
            $currency->setExchangeRate($newExchangeRate);
        }
    }

    /**
     * @return int[]
     */
    public function getNotAllowedToDeleteCurrencyIds()
    {
        $notAllowedToDeleteCurrencyIds = [$this->getDefaultCurrency()->getId()];
        foreach ($this->domain->getAll() as $domainConfig) {
            $notAllowedToDeleteCurrencyIds[] = $this->pricingSetting->getDomainDefaultCurrencyIdByDomainId(
                $domainConfig->getId()
            );
        }
        foreach ($this->getCurrenciesUsedInOrders() as $currency) {
            $notAllowedToDeleteCurrencyIds[] = $currency->getId();
        }

        return array_unique($notAllowedToDeleteCurrencyIds);
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
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $inputCurrency
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $outputCurrency
     * @return \Litipk\BigNumbers\Decimal
     */
    public function getExchangeRateForCurrencies(Currency $inputCurrency, Currency $outputCurrency): Decimal
    {
        $inputCurrencyExchangeRate = Decimal::fromString($inputCurrency->getExchangeRate());
        $outputCurrencyExchangeRate = Decimal::fromString($outputCurrency->getExchangeRate());

        return $inputCurrencyExchangeRate->div($outputCurrencyExchangeRate, 6);
    }
}
