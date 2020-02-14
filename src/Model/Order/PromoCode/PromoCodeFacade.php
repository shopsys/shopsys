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
     * @var \App\Model\Order\PromoCode\PromoCodeProductRepository
     */
    private $promoCodeProductRepository;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryRepository
     */
    private $promoCodeCategoryRepository;
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductFactory
     */
    private $promoCodeProductFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     * @param \App\Model\Order\PromoCode\PromoCodeProductRepository $promoCodeProductRepository
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryRepository $promoCodeCategoryRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        Domain $domain,
        DateTimeHelper $dateTimeHelper,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository,
        PromoCodeProductFactory $promoCodeProductFactory
    ) {
        parent::__construct($em, $promoCodeRepository, $promoCodeFactory);
        $this->domain = $domain;
        $this->dateTimeHelper = $dateTimeHelper;
        $this->promoCodeProductRepository = $promoCodeProductRepository;
        $this->promoCodeCategoryRepository = $promoCodeCategoryRepository;
        $this->promoCodeProductFactory = $promoCodeProductFactory;
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
        $this->refreshPromoCodeProducts($promoCode, $promoCodeData->productsWithSale);
        $this->refreshPromoCodeCategories($promoCode, $promoCodeData->categoriesWithSale);

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
        $this->refreshPromoCodeProducts($promoCode, $promoCodeData->productsWithSale);
        $this->refreshPromoCodeCategories($promoCode, $promoCodeData->categoriesWithSale);

        return $promoCode;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param array $categories
     */
    private function refreshPromoCodeCategories(PromoCode $promoCode, array $categories): void
    {
        $needFlush = false;
        $categoryIdsFromForm = [];
        $categoryIdsFromStorage = [];
        foreach ($categories as $category) {
            $categoryIdsFromForm[$category->getId()] = $category->getId();
        }

        $promoCodeCategories = $this->promoCodeCategoryRepository->getAllByPromoCodeId($promoCode->getId());
        foreach ($promoCodeCategories as $promoCodeCategory) {
            $categoryId = $promoCodeCategory->getCategory()->getId();
            if (in_array($categoryId, $categoryIdsFromForm, true) === false) {
                $this->em->remove($promoCodeCategory);
                $needFlush = true;
            } else {
                $categoryIdsFromStorage[$categoryId] = $categoryId;
            }
        }

        foreach ($categories as $category) {
            if (in_array($category->getId(), $categoryIdsFromStorage, true) === false) {
                $promoCodeCategory = new PromoCodeCategory($promoCode, $category);
                $this->em->persist($promoCodeCategory);
                $needFlush = true;
            }
        }

        if ($needFlush === true) {
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \App\Model\Product\Product[] $products
     */
    private function refreshPromoCodeProducts(PromoCode $promoCode, array $products): void
    {
        $needFlush = false;
        $productIdsFromForm = [];
        $productIdsFromStorage = [];
        foreach ($products as $product) {
            $productIdsFromForm[$product->getId()] = $product->getId();
        }

        $promoCodeProducts = $this->promoCodeProductRepository->getAllByPromoCodeId($promoCode->getId());
        foreach ($promoCodeProducts as $promoCodeProduct) {
            $productId = $promoCodeProduct->getProduct()->getId();
            if (in_array($productId, $productIdsFromForm, true) === false) {
                $this->em->remove($promoCodeProduct);
                $needFlush = true;
            } else {
                $productIdsFromStorage[$productId] = $productId;
            }
        }

        if ($needFlush === true) {
            $this->em->flush();
        }

        foreach ($products as $product) {
            if (in_array($product->getId(), $productIdsFromStorage, true) === false) {
                $this->promoCodeProductFactory->create($promoCode, $product);
            }
        }
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

        $promoCodeData->timeValidTo = $promoCodeData->timeValidTo ? $promoCodeData->timeValidTo . ':59' : self::PROMOCODE_DEFAULT_TIME_TO;

        if ($promoCodeData->dateValidTo !== null) {
            $promoCodeData->datetimeValidTo = $this->createDateTimeInUtc(
                $promoCodeData->dateValidTo,
                $promoCodeData->timeValidTo
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
        return $this->dateTimeHelper->convertDatetimeStringFromDisplayTimeZoneToUtc(
            $date->format(self::DATABASE_DATE_FORMAT) . 'T' . $time
        );
    }
}
