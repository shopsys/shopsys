<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CurrencyFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CurrencyRepository $currencyRepository,
        protected readonly PricingSetting $pricingSetting,
        protected readonly OrderRepository $orderRepository,
        protected readonly Domain $domain,
        protected readonly CurrencyFactory $currencyFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getById(int $currencyId): Currency
    {
        return $this->currencyRepository->getById($currencyId);
    }

    public function getByCode(string $currencyCode): Currency
    {
        return $this->currencyRepository->getByCode($currencyCode);
    }

    public function findByCode(string $currencyCode): ?Currency
    {
        return $this->currencyRepository->findByCode($currencyCode);
    }

    public function create(CurrencyData $currencyData): Currency
    {
        $currency = $this->currencyFactory->create($currencyData);
        $this->em->persist($currency);
        $this->em->flush();

        $this->dispatchCurrencyEvent($currency, CurrencyEvent::CREATE);

        return $currency;
    }

    public function edit(
        int $currencyId,
        CurrencyData $currencyData,
    ): Currency {
        $currency = $this->currencyRepository->getById($currencyId);
        $currency->edit($currencyData);

        if ($this->isDefaultCurrency($currency)) {
            $currency->setExchangeRate(Currency::DEFAULT_EXCHANGE_RATE);
        } else {
            $currency->setExchangeRate($currencyData->exchangeRate);
        }
        $this->em->flush();

        $this->dispatchCurrencyEvent($currency, CurrencyEvent::UPDATE);

        return $currency;
    }

    public function deleteById(int $currencyId): void
    {
        $currency = $this->currencyRepository->getById($currencyId);

        if (in_array($currency->getId(), $this->getNotAllowedToDeleteCurrencyIds(), true)) {
            throw new DeletingNotAllowedToDeleteCurrencyException();
        }

        $this->dispatchCurrencyEvent($currency, CurrencyEvent::DELETE);

        $this->em->remove($currency);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getAll(): array
    {
        return $this->currencyRepository->getAll();
    }

    public function getDefaultCurrency(): Currency
    {
        return $this->getById($this->pricingSetting->getDefaultCurrencyId());
    }

    public function getDomainDefaultCurrencyByDomainId(
        int $domainId,
    ): Currency {
        return $this->getByCode($this->domain->getDomainConfigById($domainId)->getDefaultCurrencyCode());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getEnabledCurrenciesByDomainId(int $domainId): array
    {
        return array_map(
            fn (string $currencyCode): Currency => $this->getByCode($currencyCode),
            $this->domain->getDomainConfigById($domainId)->getCurrencyCodes(),
        );
    }

    public function setDefaultCurrency(Currency $currency): void
    {
        $originalDefaultCurrency = $this->getDefaultCurrency();
        $this->pricingSetting->setDefaultCurrency($currency);
        $this->recalculateExchangeRatesByNewDefaultCurrency($originalDefaultCurrency, $currency);
        $this->em->flush();
    }

    protected function recalculateExchangeRatesByNewDefaultCurrency(
        Currency $originalDefaultCurrency,
        Currency $newDefaultCurrency,
    ): void {
        $coefficient = $this->getExchangeRateForCurrencies($originalDefaultCurrency, $newDefaultCurrency);

        foreach ($this->getAll() as $currency) {
            if ($currency->getId() === $newDefaultCurrency->getId()) {
                $newExchangeRate = Currency::DEFAULT_EXCHANGE_RATE;
            } else {
                $newExchangeRate = (string)BigDecimal::of($currency->getExchangeRate())->multipliedBy($coefficient);
            }
            $currency->setExchangeRate($newExchangeRate);
        }
    }

    /**
     * @return int[]
     */
    public function getNotAllowedToDeleteCurrencyIds(): array
    {
        $notAllowedToDeleteCurrencyIds = [$this->getDefaultCurrency()->getId()];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            foreach ($domainConfig->getCurrencyCodes() as $currencyCode) {
                $currency = $this->findByCode($currencyCode);

                if ($currency !== null) {
                    $notAllowedToDeleteCurrencyIds[] = $currency->getId();
                }
            }
        }

        foreach ($this->getCurrenciesUsedInOrders() as $currency) {
            $notAllowedToDeleteCurrencyIds[] = $currency->getId();
        }

        return array_unique($notAllowedToDeleteCurrencyIds);
    }

    public function isDefaultCurrency(Currency $currency): bool
    {
        return $currency === $this->getDefaultCurrency();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getCurrenciesUsedInOrders(): array
    {
        return $this->orderRepository->getCurrenciesUsedInOrders();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[]
     */
    public function getAllIndexedById(): array
    {
        $currenciesIndexedById = [];

        foreach ($this->getAll() as $currency) {
            $currenciesIndexedById[$currency->getId()] = $currency;
        }

        return $currenciesIndexedById;
    }

    public function getExchangeRateForCurrencies(Currency $inputCurrency, Currency $outputCurrency): BigDecimal
    {
        $inputCurrencyExchangeRate = BigDecimal::of($inputCurrency->getExchangeRate());
        $outputCurrencyExchangeRate = BigDecimal::of($outputCurrency->getExchangeRate());

        return $inputCurrencyExchangeRate->dividedBy($outputCurrencyExchangeRate, 6, RoundingMode::HALF_UP);
    }

    /**
     * @see \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyEvent class
     */
    protected function dispatchCurrencyEvent(Currency $currency, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new CurrencyEvent($currency), $eventType);
    }
}
