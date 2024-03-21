<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Transport\Transport;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class PaymentDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '6ffdbcc0-fd5d-4f60-bf8d-1349366b3d93';

    public const PAYMENT_CARD = 'payment_card';
    public const PAYMENT_CASH_ON_DELIVERY = 'payment_cash_on_delivery';
    public const PAYMENT_CASH = 'payment_cash';
    public const PAYMENT_GOPAY_DOMAIN = Payment::TYPE_GOPAY . '_domain_';
    public const PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN = 'goPay_bank_account_transfer_domain_';
    public const PAYMENT_LATER = 'payment_later';

    /**
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Payment\PaymentDataFactory $paymentDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     */
    public function __construct(
        private readonly PaymentFacade $paymentFacade,
        private readonly PaymentDataFactory $paymentDataFactory,
        private readonly Domain $domain,
        private readonly PriceConverter $priceConverter,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Credit card', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $paymentData->description[$locale] = t('Quick, cheap and reliable!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $paymentData->instructions[$locale] = t(
                '<b>You have chosen payment by credit card. Please finish it in two business days.</b>',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('99.95'));

        $this->createPayment(self::PAYMENT_CARD, $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_PPL,
        ]);

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('49.90'));
        $this->createPayment(
            self::PAYMENT_CASH_ON_DELIVERY,
            $paymentData,
            [
                TransportDataFixture::TRANSPORT_CZECH_POST,
                TransportDataFixture::TRANSPORT_PPL,
            ],
        );

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Cash', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $paymentData->czkRounding = true;

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::zero());
        $this->createPayment(self::PAYMENT_CASH, $paymentData, [TransportDataFixture::TRANSPORT_PERSONAL]);

        $this->createGoPayCardPaymentOnDomain($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));
        $this->createGoPayBankAccountTransferPaymentOnDomain($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));

        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_BASIC;

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Pay later', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('199.90'));
        $this->createPayment(self::PAYMENT_LATER, $paymentData, [TransportDataFixture::TRANSPORT_DRONE]);

        foreach ($this->domain->getAll() as $domainConfig) {
            if ($domainConfig->getId() === Domain::FIRST_DOMAIN_ID) {
                continue;
            }
            $this->createGoPayCardPaymentOnDomain($domainConfig);
            $this->createGoPayBankAccountTransferPaymentOnDomain($domainConfig);
        }
    }

    /**
     * @param string $referenceName
     * @param \App\Model\Payment\PaymentData $paymentData
     * @param array $transportsReferenceNames
     */
    private function createPayment(
        string $referenceName,
        PaymentData $paymentData,
        array $transportsReferenceNames,
    ) {
        $paymentData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();
        $paymentData->transports = [];

        foreach ($transportsReferenceNames as $transportReferenceName) {
            $transport = $this->getReference($transportReferenceName, Transport::class);
            $paymentData->transports[] = $transport;
        }

        $payment = $this->paymentFacade->create($paymentData);
        $this->addReference($referenceName, $payment);
    }

    /**
     * {@inheritdoc}
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
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domain->getId(), Vat::class);

            $convertedPrice = $this->priceConverter->convertPriceToInputPriceWithoutVatInDomainDefaultCurrency(
                $price,
                $currencyCzk,
                $vat->getPercent(),
                $domain->getId(),
            );

            $paymentData->pricesIndexedByDomainId[$domain->getId()] = $convertedPrice;
            $paymentData->vatsIndexedByDomainId[$domain->getId()] = $vat;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function createGoPayBankAccountTransferPaymentOnDomain(DomainConfig $domainConfig): void
    {
        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_GOPAY;

        foreach ($this->domain->getAll() as $domain) {
            $domainId = $domain->getId();
            $paymentData->enabled[$domainId] = $domainId === $domainConfig->getId();
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('GoPay - Quick Bank Account Transfer [%locale%]', ['%locale%' => $domainConfig->getLocale()], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $paymentData->description[$locale] = t('Quick and Safe payment via bank account transfer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $paymentData->instructions[$locale] = t('<b>You have chosen GoPay Payment, you will be shown a payment gateway.</b>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $paymentData->czkRounding = false;
        $paymentData->goPayPaymentMethod = $this->getReferenceForDomain(GoPayDataFixture::BANK_ACCOUNT_METHOD, $domainConfig->getId(), GoPayPaymentMethod::class);
        $paymentData->hidden = false;
        $this->createPayment(self::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . $domainConfig->getId(), $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_CZECH_POST,
            TransportDataFixture::TRANSPORT_PPL,
            TransportDataFixture::TRANSPORT_DRONE,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    private function createGoPayCardPaymentOnDomain(DomainConfig $domainConfig): void
    {
        $paymentData = $this->paymentDataFactory->create();
        $paymentData->type = Payment::TYPE_GOPAY;

        foreach ($this->domain->getAll() as $domain) {
            $domainId = $domain->getId();
            $paymentData->enabled[$domainId] = $domainId === $domainConfig->getId();
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('GoPay - Payment By Card [%locale%]', ['%locale%' => $domainConfig->getLocale()], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $paymentData->description[$locale] = '';
            $paymentData->instructions[$locale] = t('<b>You have chosen GoPay Payment, you will be shown a payment gateway.</b>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        }
        $paymentData->czkRounding = false;

        $paymentData->goPayPaymentMethod = $this->getReferenceForDomain(GoPayDataFixture::PAYMENT_CARD_METHOD, $domainConfig->getId(), GoPayPaymentMethod::class);

        $paymentData->hidden = false;
        $this->createPayment(self::PAYMENT_GOPAY_DOMAIN . $domainConfig->getId(), $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_PPL,
        ]);
    }
}
