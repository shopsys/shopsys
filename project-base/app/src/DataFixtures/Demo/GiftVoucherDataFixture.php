<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDataFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherGenerationFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherStatusEnum;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;

class GiftVoucherDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string GIFT_VOUCHER_UNREDEEMED = 'gift_voucher_unredeemed';
    public const string GIFT_VOUCHER_REDEEMED = 'gift_voucher_redeemed';
    public const string GIFT_VOUCHER_CANCELLED = 'gift_voucher_cancelled';
    public const string GIFT_VOUCHER_EXPIRED = 'gift_voucher_expired';
    public const string GIFT_VOUCHER_DOMAIN_PREFIX = 'gift_voucher_domain_';
    public const string GIFT_VOUCHER_FULL_PAYMENT = 'gift_voucher_full_payment';

    public const string GIFT_VOUCHER_UNREDEEMED_CODE = 'HAPPYDAY2345';
    public const string GIFT_VOUCHER_REDEEMED_CODE = 'REDEEMED2345';
    public const string GIFT_VOUCHER_CANCELLED_CODE = 'XWVTRPNMKJHF';
    public const string GIFT_VOUCHER_EXPIRED_CODE = 'EXPIRED23456';
    public const string GIFT_VOUCHER_SECOND_DOMAIN_CODE = 'DVADVADVA234';
    public const string GIFT_VOUCHER_FULL_PAYMENT_CODE = 'MAXPAY234567';

    public const string GIFT_VOUCHER_FULL_PAYMENT_VALUE = '22.44';

    public function __construct(
        private readonly GiftVoucherFacade $giftVoucherFacade,
        private readonly GiftVoucherDataFactory $giftVoucherDataFactory,
        private readonly CurrencyFacade $currencyFacade,
        private readonly EntityManagerInterface $em,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $sourceOrder = $this->getReference(OrderDataFixture::ORDER_WITH_GIFT_VOUCHER_PRODUCTS, Order::class);

        $this->createGiftVoucher(self::GIFT_VOUCHER_UNREDEEMED, self::GIFT_VOUCHER_UNREDEEMED_CODE, Domain::FIRST_DOMAIN_ID, GiftVoucherStatusEnum::STATUS_UNREDEEMED, $sourceOrder);

        $redeemedGiftVoucher = $this->createGiftVoucher(self::GIFT_VOUCHER_REDEEMED, self::GIFT_VOUCHER_REDEEMED_CODE, Domain::FIRST_DOMAIN_ID, GiftVoucherStatusEnum::STATUS_UNREDEEMED, $sourceOrder);
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '1', Order::class);
        $redeemedGiftVoucher->markAsRedeemed($order, new DateTimeImmutable());
        $this->em->flush();

        $this->createGiftVoucher(
            self::GIFT_VOUCHER_CANCELLED,
            self::GIFT_VOUCHER_CANCELLED_CODE,
            Domain::FIRST_DOMAIN_ID,
            GiftVoucherStatusEnum::STATUS_CANCELLED,
            $sourceOrder,
        );

        $this->createGiftVoucher(
            self::GIFT_VOUCHER_EXPIRED,
            self::GIFT_VOUCHER_EXPIRED_CODE,
            Domain::FIRST_DOMAIN_ID,
            GiftVoucherStatusEnum::STATUS_UNREDEEMED,
            $sourceOrder,
            activatedAt: (new DateTimeImmutable())->modify('-400 days'),
        );

        $this->createGiftVoucher(
            self::GIFT_VOUCHER_FULL_PAYMENT,
            self::GIFT_VOUCHER_FULL_PAYMENT_CODE,
            Domain::FIRST_DOMAIN_ID,
            GiftVoucherStatusEnum::STATUS_UNREDEEMED,
            $sourceOrder,
            Money::create(self::GIFT_VOUCHER_FULL_PAYMENT_VALUE),
        );

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();

            if ($domainId === Domain::FIRST_DOMAIN_ID) {
                continue;
            }

            $code = $domainId === Domain::SECOND_DOMAIN_ID
                ? self::GIFT_VOUCHER_SECOND_DOMAIN_CODE
                : sprintf('DOMAIN%dVOUCHER', $domainId);

            $this->createGiftVoucher(self::GIFT_VOUCHER_DOMAIN_PREFIX . $domainId, $code, $domainId);
        }
    }

    private function createGiftVoucher(
        string $referenceName,
        string $code,
        int $domainId,
        string $status = GiftVoucherStatusEnum::STATUS_UNREDEEMED,
        ?Order $sourceOrder = null,
        ?Money $value = null,
        ?DateTimeImmutable $activatedAt = null,
    ): GiftVoucher {
        $giftVoucherData = $this->giftVoucherDataFactory->create();
        $giftVoucherData->code = $code;
        $giftVoucherData->domainId = $domainId;
        $giftVoucherData->valueWithVat = $value ?? $this->getDefaultValueWithVatForDomain($domainId);
        $giftVoucherData->currencyCode = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId)->getCode();
        $giftVoucherData->status = $status;
        $giftVoucherData->activatedAt = $activatedAt ?? new DateTimeImmutable();
        $giftVoucherData->validUntil = $giftVoucherData->activatedAt->modify(GiftVoucherGenerationFacade::VALIDITY_MODIFIER);
        $giftVoucherData->customerEmail = 'no-reply@shopsys.com';

        if ($sourceOrder !== null) {
            $sourceOrderItem = array_first($sourceOrder->getProductItems());
            $giftVoucherData->createdOnOrder = $sourceOrder;
            $giftVoucherData->productCatnum = $sourceOrderItem->getProduct()?->getCatnum();
            $giftVoucherData->productName = $sourceOrderItem->getName();
        }

        $giftVoucher = $this->giftVoucherFacade->create($giftVoucherData);
        $this->addReference($referenceName, $giftVoucher);

        return $giftVoucher;
    }

    private function getDefaultValueWithVatForDomain(int $domainId): Money
    {
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        return $this->priceConverter->convertPriceToInputPriceInDomainDefaultCurrency(
            Money::create(1000),
            $currencyCzk,
            '0',
            $domainId,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            OrderDataFixture::class,
            CurrencyDataFixture::class,
        ];
    }
}
