<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Payment\Payment;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;

class PaymentDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PAYMENT_CARD = 'payment_card';
    public const PAYMENT_CASH_ON_DELIVERY = 'payment_cash_on_delivery';
    public const PAYMENT_CASH = 'payment_cash';
    public const PAYMENT_GOPAY = Payment::TYPE_GOPAY;
    public const PAYMENT_GOPAY_BANK_ACCOUNT = 'goPay_bank_account_transfer';
    public const PAYMENT_OVER_LIMIT = 'payment_over_limit';

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '60b0df97-047f-48e4-8864-1d90ba1aaf84', '5a8d0623-fecc-432e-a4a7-330e96da2dab',
        '7adc774b-aa39-4727-b373-544345814929', '1dd4fd71-3d82-48cb-b2b0-eecff0f297d3',
        'a22b0dde-77ab-448f-be5e-831c0b2b5a32',
    ];

    /**
     * @var \App\Model\Payment\PaymentFacade
     */
    private $paymentFacade;

    /**
     * @var \App\Model\Payment\PaymentDataFactory
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    private $priceConverter;

    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Payment\PaymentDataFactory $paymentDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        PaymentDataFactoryInterface $paymentDataFactory,
        Domain $domain,
        PriceConverter $priceConverter
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->paymentDataFactory = $paymentDataFactory;
        $this->domain = $domain;
        $this->priceConverter = $priceConverter;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Credit card', [], 'dataFixtures', $locale);
            $paymentData->description[$locale] = t('Quick, cheap and reliable!', [], 'dataFixtures', $locale);
            $paymentData->instructions[$locale] = t('<b>You have chosen payment by credit card. Please finish it in two business days.</b>', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('99.95'));

        $this->createPayment(self::PAYMENT_CARD, $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_PPL,
        ]);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Cash on delivery', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('49.90'));
        $this->createPayment(self::PAYMENT_CASH_ON_DELIVERY, $paymentData, [TransportDataFixture::TRANSPORT_CZECH_POST]);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Cash', [], 'dataFixtures', $locale);
        }

        $paymentData->czkRounding = true;

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::zero());
        $this->createPayment(self::PAYMENT_CASH, $paymentData, [TransportDataFixture::TRANSPORT_PERSONAL]);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_GOPAY;
        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('GoPay - Payment By Card', [], 'dataFixtures', $locale);
            $paymentData->description[$locale] = '';
            $paymentData->instructions[$locale] = t('<b>You have chosen GoPay Payment, you will be shown a payment gateway.</b>', [], 'dataFixtures', $locale);
        }
        $paymentData->czkRounding = false;

        $paymentData->goPayPaymentMethod = $this->getReference(GoPayDataFixture::PAYMENT_CARD_METHOD);

        $paymentData->hidden = false;
        $this->createPayment(self::PAYMENT_GOPAY, $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_PPL,
        ]);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = self::PAYMENT_GOPAY;
        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('GoPay - Quick Bank Account Transfer', [], 'dataFixtures', $locale);
            $paymentData->description[$locale] = t('Quick and Safe payment via bank account transfer.', [], 'dataFixtures', $locale);
            $paymentData->instructions[$locale] = t('<b>You have chosen GoPay Payment, you will be shown a payment gateway.</b>', [], 'dataFixtures', $locale);
        }
        $paymentData->czkRounding = false;
        $paymentData->goPayPaymentMethod = $this->getReference(GoPayDataFixture::BANK_ACCOUNT_METHOD);
        $paymentData->hidden = false;
        $this->createPayment(self::PAYMENT_GOPAY_BANK_ACCOUNT, $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_CZECH_POST,
            TransportDataFixture::TRANSPORT_PPL,
            TransportDataFixture::TRANSPORT_OVER_LIMIT,
        ]);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Nadlimitní', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('199.90'));
        $this->createPayment(self::PAYMENT_OVER_LIMIT, $paymentData, [TransportDataFixture::TRANSPORT_OVER_LIMIT]);
    }

    /**
     * @param string $referenceName
     * @param \App\Model\Payment\PaymentData $paymentData
     * @param array $transportsReferenceNames
     */
    private function createPayment(
        $referenceName,
        PaymentData $paymentData,
        array $transportsReferenceNames
    ) {
        $paymentData->uuid = array_pop($this->uuidPool);
        $paymentData->transports = [];
        foreach ($transportsReferenceNames as $transportReferenceName) {
            /** @var \App\Model\Transport\Transport $transport */
            $transport = $this->getReference($transportReferenceName);
            $paymentData->transports[] = $transport;
        }

        $payment = $this->paymentFacade->create($paymentData);
        $this->addReference($referenceName, $payment);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            TransportDataFixture::class,
            VatDataFixture::class,
            CurrencyDataFixture::class,
            GoPayDataFixture::class,
            SettingValueDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    private function setPriceForAllDomainDefaultCurrencies(PaymentData $paymentData, Money $price): void
    {
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domain->getId());
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
            $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);

            $convertedPrice = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
                $price,
                $currencyCzk,
                $vat->getPercent(),
                $domain->getId()
            );

            $paymentData->pricesIndexedByDomainId[$domain->getId()] = $convertedPrice;
            $paymentData->vatsIndexedByDomainId[$domain->getId()] = $vat;
        }
    }
}
