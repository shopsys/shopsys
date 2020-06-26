<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\DateTimeHelper\DateTimeHelper;
use DateTime;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory as BasePromoCodeDataFactory;

class PromoCodeDataFactory extends BasePromoCodeDataFactory
{
    public const TIME_VALID_FORMAT = 'H:i';

    /**
     * @var \App\Component\DateTimeHelper\DateTimeHelper
     */
    private $dateTimeHelper;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryRepository
     */
    private $promoCodeCategoryRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductRepository
     */
    private $promoCodeProductRepository;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     */
    public function __construct(
        PromoCodeCategoryRepository $promoCodeCategoryRepository,
        PromoCodeProductRepository $promoCodeProductRepository,
        DateTimeHelper $dateTimeHelper
    ) {
        $this->dateTimeHelper = $dateTimeHelper;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
        $this->promoCodeProductRepository = $promoCodeProductRepository;
    }

    /**
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): BasePromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $promoCodeData->massGenerate = false;

        return $promoCodeData;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function createFromPromoCode(BasePromoCode $promoCode): BasePromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function fillFromPromoCode(BasePromoCodeData $promoCodeData, BasePromoCode $promoCode)
    {
        parent::fillFromPromoCode($promoCodeData, $promoCode);
        $promoCodeData->domainId = $promoCode->getDomainId();

        $promoCodeData->timeValidFrom = $this->formatTimeFromValidDateTime($promoCode->getDatetimeValidFrom());
        $promoCodeData->timeValidTo = $this->formatTimeFromValidDateTime($promoCode->getDatetimeValidTo());

        $promoCodeData->dateValidFrom = $this->switchDateFromDatabaseTimeZoneToViewTimezone($promoCode->getDatetimeValidFrom());
        $promoCodeData->dateValidTo = $this->switchDateFromDatabaseTimeZoneToViewTimezone($promoCode->getDatetimeValidTo());

        $promoCodeData->categoriesWithSale = $this->promoCodeCategoryRepository->getCategoriesByPromoCodeId($promoCode->getId());
        $promoCodeData->productsWithSale = $this->promoCodeProductRepository->getProductsByPromoCodeId($promoCode->getId());
        $promoCodeData->remainingUses = $promoCode->getRemainingUses();
        $promoCodeData->identifier = $promoCode->getIdentifier();
        $promoCodeData->massGenerate = $promoCode->isMassGenerate();
        $promoCodeData->prefix = $promoCode->getPrefix();
        $promoCodeData->applyOnSecondProduct = $promoCode->isApplyOnSecondProduct();
        $promoCodeData->onSale = $promoCode->isOnSale();
        $promoCodeData->inAction = $promoCode->isInAction();
        $promoCodeData->scontoPrice = $promoCode->isScontoPrice();
        $promoCodeData->withoutLowPrice = $promoCode->isWithoutLowPrice();
    }

    /**
     * @param \DateTime|null $dateTime
     * @return \DateTime|null
     */
    private function switchDateFromDatabaseTimeZoneToViewTimezone(?DateTime $dateTime = null): ?DateTime
    {
        if ($dateTime !== null) {
            return new DateTime($dateTime->format('Y-m-d H:i:s'), new \DateTimeZone(DateTimeHelper::UTC_TIMEZONE));
        }

        return null;
    }

    /**
     * @param \DateTime|null $dateTime
     * @return string|null
     */
    private function formatTimeFromValidDateTime(?DateTime $dateTime = null): ?string
    {
        if ($dateTime !== null) {
            $this->dateTimeHelper->convertDateTimeFromUtcToDisplayTimeZone($dateTime);
            return $dateTime->format(self::TIME_VALID_FORMAT);
        }

        return null;
    }
}
