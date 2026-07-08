<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class FreeTransportAndPaymentFacade
{
    public function __construct(
        protected readonly PricingSetting $pricingSetting,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
        protected readonly FreeTransportAndPaymentPriceLimitRepository $freeTransportAndPaymentPriceLimitRepository,
        protected readonly FreeTransportAndPaymentPriceLimitFactory $freeTransportAndPaymentPriceLimitFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function isActive(int $domainId, bool $forceFreeTransportAndPayment, Currency $currency): bool
    {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        if ($forceFreeTransportAndPayment) {
            return true;
        }

        return $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId, $currency) !== null;
    }

    public function isFree(
        PriceInterface $productsPrice,
        int $domainId,
        bool $forceFreeTransportAndPayment,
        Currency $currency,
    ): bool {
        if (!$this->customerUserRoleResolver->canCurrentCustomerUserSeePrices()) {
            return false;
        }

        if ($forceFreeTransportAndPayment) {
            return true;
        }

        $remainingFreeTransportAmount = $this->getDifferenceAmountBetweenLimitAndProductsPrice($productsPrice, $domainId, $currency);

        if ($remainingFreeTransportAmount === null) {
            return false;
        }

        return $remainingFreeTransportAmount->isLessThanOrEqualTo(Money::zero());
    }

    public function getRemainingAmount(
        PriceInterface $productsPrice,
        int $domainId,
        bool $forceFreeTransportAndPayment,
        Currency $currency,
    ): Money {
        if (!$this->isFree($productsPrice, $domainId, $forceFreeTransportAndPayment, $currency) && $this->isActive($domainId, $forceFreeTransportAndPayment, $currency)) {
            return $this->getDifferenceAmountBetweenLimitAndProductsPrice($productsPrice, $domainId, $currency);
        }

        return Money::zero();
    }

    protected function getFreeTransportAndPaymentPriceLimitOnDomain(int $domainId, Currency $currency): ?Money
    {
        return $this->freeTransportAndPaymentPriceLimitRepository
            ->findByDomainIdAndCurrency($domainId, $currency)
            ?->getPrice();
    }

    public function isFreeTransportAndPaymentApplied(
        int $domainId,
        PriceInterface $productsPrice,
        bool $forceFreeTransportAndPayment,
        Currency $currency,
    ): bool {
        return $this->isActive($domainId, $forceFreeTransportAndPayment, $currency) && $this->getRemainingAmount($productsPrice, $domainId, $forceFreeTransportAndPayment, $currency)->isZero();
    }

    protected function getDifferenceAmountBetweenLimitAndProductsPrice(
        PriceInterface $productsPrice,
        int $domainId,
        Currency $currency,
    ): ?Money {
        $limit = $this->getFreeTransportAndPaymentPriceLimitOnDomain($domainId, $currency);

        if ($limit === null) {
            return null;
        }

        if ($this->pricingSetting->getInputPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT) {
            return $limit->subtract($productsPrice->getPriceWithVat());
        }

        return $limit->subtract($productsPrice->getPriceWithoutVat());
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Money\Money>
     */
    public function getPriceLimitsIndexedByCurrencyCode(int $domainId): array
    {
        $priceLimitsByCurrencyCode = [];

        foreach ($this->freeTransportAndPaymentPriceLimitRepository->getAllByDomainId($domainId) as $priceLimit) {
            $priceLimitsByCurrencyCode[$priceLimit->getCurrency()->getCode()] = $priceLimit->getPrice();
        }

        return $priceLimitsByCurrencyCode;
    }

    /**
     * Null replaces all price limits of the domain (the free transport and payment is disabled on the domain)
     *
     * @param array<string, \Shopsys\FrameworkBundle\Component\Money\Money>|null $pricesByCurrencyCode
     */
    public function setPriceLimits(int $domainId, ?array $pricesByCurrencyCode): void
    {
        foreach ($this->freeTransportAndPaymentPriceLimitRepository->getAllByDomainId($domainId) as $priceLimit) {
            $this->em->remove($priceLimit);
        }
        $this->em->flush();

        foreach ($pricesByCurrencyCode ?? [] as $currencyCode => $price) {
            $priceLimit = $this->freeTransportAndPaymentPriceLimitFactory->create(
                $domainId,
                $this->currencyFacade->getByCode($currencyCode),
                $price,
            );
            $this->em->persist($priceLimit);
        }
        $this->em->flush();
    }
}
