<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\String\HashGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 * @method \App\Model\Order\PromoCode\PromoCode create(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode edit(int $promoCodeId, \App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode|null findPromoCodeByCodeAndDomain(string $code, int $domainId)
 * @method refreshPromoCodeRelations(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method refreshPromoCodeLimits(\App\Model\Order\PromoCode\PromoCode $promoCode, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[] $limits)
 */
class PromoCodeFacade extends BasePromoCodeFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory $promoCodeProductFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory $promoCodeCategoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandFactory $promoCodeBrandFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupFactory $promoCodePricingGroupFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     * @param \App\Component\String\HashGenerator $hashGenerator
     */
    public function __construct(
        EntityManagerInterface $em,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeFactoryInterface $promoCodeFactory,
        PromoCodeLimitRepository $promoCodeLimitRepository,
        PromoCodeProductRepository $promoCodeProductRepository,
        PromoCodeCategoryRepository $promoCodeCategoryRepository,
        PromoCodeProductFactory $promoCodeProductFactory,
        PromoCodeCategoryFactory $promoCodeCategoryFactory,
        PromoCodeBrandRepository $promoCodeBrandRepository,
        PromoCodeBrandFactory $promoCodeBrandFactory,
        PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        PromoCodePricingGroupFactory $promoCodePricingGroupFactory,
        PromoCodeFlagRepository $promoCodeFlagRepository,
        private readonly HashGenerator $hashGenerator,
    ) {
        parent::__construct(
            $em,
            $promoCodeRepository,
            $promoCodeFactory,
            $promoCodeLimitRepository,
            $promoCodeProductRepository,
            $promoCodeCategoryRepository,
            $promoCodeProductFactory,
            $promoCodeCategoryFactory,
            $promoCodeBrandRepository,
            $promoCodeBrandFactory,
            $promoCodePricingGroupRepository,
            $promoCodePricingGroupFactory,
            $promoCodeFlagRepository,
        );
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function massCreate(PromoCodeData $promoCodeData): void
    {
        $existingPromoCodeCodes = $this->promoCodeRepository->getAllPromoCodeCodes();
        $generatedPromoCodeCount = 0;

        while ($generatedPromoCodeCount < $promoCodeData->quantity) {
            $promoCodeDataForCreate = clone $promoCodeData;
            $code = $promoCodeDataForCreate->prefix . strtoupper($this->hashGenerator->generateHashWithoutConfusingCharacters(PromoCode::MASS_GENERATED_CODE_LENGTH));

            if (in_array($code, $existingPromoCodeCodes, true)) {
                continue;
            }

            $promoCodeDataForCreate->code = $code;

            $this->create($promoCodeDataForCreate);

            $existingPromoCodeCodes[] = $code;
            $generatedPromoCodeCount++;
        }
    }

    /**
     * @return int
     */
    public function getMassLastGeneratedBatchId(): int
    {
        return $this->promoCodeRepository->getMassLastGeneratedBatchId();
    }

    /**
     * @param int $batchId
     * @return \App\Model\Order\PromoCode\PromoCode[]|null
     */
    public function findByMassBatchId(int $batchId): ?array
    {
        return $this->promoCodeRepository->findByMassBatchId($batchId);
    }
}
