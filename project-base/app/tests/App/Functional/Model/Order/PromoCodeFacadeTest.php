<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order;

use App\DataFixtures\Demo\FlagDataFixture;
use App\DataFixtures\Demo\PromoCodeDataFixture;
use App\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

final class PromoCodeFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @inject
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    /**
     * @inject
     */
    private PromoCodeLimitFactory $promoCodeLimitFactory;

    /**
     * @inject
     */
    private PromoCodeFlagFactory $promoCodeFlagFactory;

    /**
     * @inject
     */
    private PromoCodeFlagRepository $promoCodeFlagRepository;

    /**
     * @inject
     */
    private PromoCodeLimitRepository $promoCodeLimitRepository;

    public function testEditPromoCodeLimits(): void
    {
        $promoCode = $this->getReferenceForDomain(
            PromoCodeDataFixture::VALID_PROMO_CODE,
            Domain::FIRST_DOMAIN_ID,
            PromoCode::class,
        );
        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);

        $expectedLimits = [
            ...$this->mapPromoCodeLimits($promoCodeData->limits),
            ['fromPrice' => '100.000000', 'discount' => '20.000000'],
        ];

        $promoCodeData->limits[] = $this->promoCodeLimitFactory->create('100', '20');

        $this->promoCodeFacade->edit($promoCode->getId(), $promoCodeData);
        $this->em->clear();

        $limits = $this->promoCodeLimitRepository->getLimitsByPromoCodeId($promoCode->getId());

        $this->assertSame(
            $expectedLimits,
            $this->mapPromoCodeLimits($limits),
        );
    }

    public function testEditPromoCodeFlags(): void
    {
        $promoCode = $this->getReferenceForDomain(
            PromoCodeDataFixture::PROMO_CODE_FOR_NEW_PRODUCT,
            Domain::FIRST_DOMAIN_ID,
            PromoCode::class,
        );
        $promoCodeData = $this->promoCodeDataFactory->createFromPromoCode($promoCode);

        $expectedFlags = [
            ...$this->mapPromoCodeFlags($promoCodeData->flags),
            ['flagId' => $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class)->getId(), 'type' => PromoCodeFlag::TYPE_EXCLUSIVE],
        ];

        $promoCodeData->flags[] = $this->promoCodeFlagFactory->create(
            $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class),
            PromoCodeFlag::TYPE_EXCLUSIVE,
        );

        $this->promoCodeFacade->edit($promoCode->getId(), $promoCodeData);
        $this->em->clear();

        $flags = $this->promoCodeFlagRepository->getFlagsByPromoCodeId($promoCode->getId());

        $this->assertEqualsCanonicalizing(
            $expectedFlags,
            $this->mapPromoCodeFlags($flags),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[] $limits
     * @return array<int, array{fromPrice: string, discount: string}>
     */
    private function mapPromoCodeLimits(array $limits): array
    {
        return array_map(
            static function (PromoCodeLimit $limit) {
                return ['fromPrice' => $limit->getFromPrice(), 'discount' => $limit->getDiscount()];
            },
            $limits,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $flags
     * @return array<int, array{flagId: int, type: string}>
     */
    private function mapPromoCodeFlags(array $flags): array
    {
        return array_map(
            static function (PromoCodeFlag $flag) {
                return ['flagId' => $flag->getFlag()->getId(), 'type' => $flag->getType()];
            },
            $flags,
        );
    }
}
