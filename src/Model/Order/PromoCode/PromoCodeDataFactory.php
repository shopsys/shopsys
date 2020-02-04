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
     * @param \App\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     */
    public function __construct(DateTimeHelper $dateTimeHelper)
    {
        $this->dateTimeHelper = $dateTimeHelper;
    }

    /**
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): BasePromoCodeData
    {
        return new PromoCodeData();
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

        $promoCodeData->dateValidFrom = $this->solveDate($promoCode->getDatetimeValidFrom());
        $promoCodeData->dateValidTo = $this->solveDate($promoCode->getDatetimeValidTo());

        $promoCodeData->timeValidFrom = $this->solveTime($promoCode->getDatetimeValidFrom());
        $promoCodeData->timeValidTo = $this->solveTime($promoCode->getDatetimeValidTo());
    }

    /**
     * @param \DateTime|null $dateTime
     * @return \DateTime|null
     */
    private function solveDate(?DateTime $dateTime = null): ?DateTime
    {
        if ($dateTime) {
            return $this->dateTimeHelper->convertDateTimeFromUtcToDisplayTimeZone($dateTime);
        }
        return null;
    }

    /**
     * @param \DateTime|null $dateTime
     * @return string|null
     */
    private function solveTime(?DateTime $dateTime = null): ?string
    {
        $dateTime = $this->solveDate($dateTime);
        if ($dateTime) {
            return $dateTime->format(self::TIME_VALID_FORMAT);
        }
        return null;
    }
}
