<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\DateTimeHelper\DateTimeHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 */
class PromoCodeFacade extends BasePromoCodeFacade
{
    public const PROMOCODE_DEFAULT_TIME_FROM = '00:00:00';
    public const PROMOCODE_DEFAULT_TIME_TO = '23:59:59';
    public const DATABASE_DATE_FORMAT = 'Y-m-d';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Component\DateTimeHelper\DateTimeHelper
     */
    private $dateTimeHelper;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        Domain $domain,
        DateTimeHelper $dateTimeHelper
    ) {
        parent::__construct($em, $promoCodeRepository, $promoCodeFactory);
        $this->domain = $domain;
        $this->dateTimeHelper = $dateTimeHelper;
    }

    /**
     * @param string $code
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function findPromoCodeByCode($code): ?PromoCode
    {
        return $this->promoCodeRepository->findByCodeAndDomainId($code, $this->domain->getId());
    }

    /**
     * @param string $code
     * @param int $domainId
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function findPromoCodeByCodeAndDomain(string $code, int $domainId): ?PromoCode
    {
        return $this->promoCodeRepository->findByCodeAndDomainId($code, $domainId);
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    public function create(PromoCodeData $promoCodeData): PromoCode
    {
        $this->prepareDatetimeValid($promoCodeData);

        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = parent::create($promoCodeData);

        return $promoCode;
    }

    /**
     * @param int $promoCodeId
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    public function edit($promoCodeId, PromoCodeData $promoCodeData): PromoCode
    {
        $this->prepareDatetimeValid($promoCodeData);

        /** @var \App\Model\Order\PromoCode\PromoCode $promoCode */
        $promoCode = parent::edit($promoCodeId, $promoCodeData);

        return $promoCode;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    private function prepareDatetimeValid(PromoCodeData $promoCodeData): void
    {
        if ($promoCodeData->dateValidFrom !== null) {
            $promoCodeData->datetimeValidFrom = $this->createDateTimeInUtc(
                $promoCodeData->dateValidFrom,
                $promoCodeData->timeValidFrom ?? self::PROMOCODE_DEFAULT_TIME_FROM
            );
        }

        if ($promoCodeData->dateValidTo !== null) {
            $promoCodeData->datetimeValidTo = $this->createDateTimeInUtc(
                $promoCodeData->dateValidTo,
                $promoCodeData->timeValidTo ?? self::PROMOCODE_DEFAULT_TIME_TO
            );
        }
    }

    /**
     * @param \DateTime $date
     * @param string $time
     * @return \DateTime
     */
    private function createDateTimeInUtc(DateTime $date, string $time): DateTime
    {
        return $this->dateTimeHelper->convertDateTimeFromDisplayTimeZoneToUtc(
            $date->format(self::DATABASE_DATE_FORMAT) . 'T' . $time
        );
    }
}
