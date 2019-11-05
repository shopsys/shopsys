<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;

class PaymentDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PAYMENT_CARD = 'payment_card';
    public const PAYMENT_CASH_ON_DELIVERY = 'payment_cash_on_delivery';
    public const PAYMENT_CASH = 'payment_cash';

    /** @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */
    protected $paymentFacade;

    /**
     * @var \App\Model\Payment\PaymentDataFactory
     */
    protected $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter
     */
    protected $priceConverter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\Payment\PaymentDataFactory $paymentDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        PaymentDataFactoryInterface $paymentDataFactory,
        Domain $domain,
        PriceConverter $priceConverter,
        CurrencyFacade $currencyFacade
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->paymentDataFactory = $paymentDataFactory;
        $this->domain = $domain;
        $this->priceConverter = $priceConverter;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $paymentData = $this->paymentDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Credit card', [], 'dataFixtures', $locale);
            $paymentData->description[$locale] = t('Quick, cheap and reliable!', [], 'dataFixtures', $locale);
            $paymentData->instructions[$locale] = t('<b>You have chosen payment by credit card. Please finish it in two business days.</b>', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('99.95'));

        $paymentData->vat = $this->getReference(VatDataFixture::VAT_ZERO);
        $this->createPayment(self::PAYMENT_CARD, $paymentData, [
            TransportDataFixture::TRANSPORT_PERSONAL,
            TransportDataFixture::TRANSPORT_PPL,
        ]);

        $paymentData = $this->paymentDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Cash on delivery', [], 'dataFixtures', $locale);
        }

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::create('49.90'));

        $paymentData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $this->createPayment(self::PAYMENT_CASH_ON_DELIVERY, $paymentData, [TransportDataFixture::TRANSPORT_CZECH_POST]);

        $paymentData = $this->paymentDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = t('Cash', [], 'dataFixtures', $locale);
        }

        $paymentData->czkRounding = true;

        $this->setPriceForAllDomainDefaultCurrencies($paymentData, Money::zero());

        $paymentData->vat = $this->getReference(VatDataFixture::VAT_HIGH);
        $this->createPayment(self::PAYMENT_CASH, $paymentData, [TransportDataFixture::TRANSPORT_PERSONAL]);
    }

    /**
     * @param string $referenceName
     * @param \App\Model\Payment\PaymentData $paymentData
     * @param array $transportsReferenceNames
     */
    protected function createPayment(
        $referenceName,
        PaymentData $paymentData,
        array $transportsReferenceNames
    ) {
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
            SettingValueDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Payment\PaymentData $paymentData
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    protected function setPriceForAllDomainDefaultCurrencies(PaymentData $paymentData, Money $price): void
    {
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domain) {
            $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domain->getId());
            $price = $this->priceConverter->convertPriceWithoutVatToPriceInDomainDefaultCurrency($price, $domain->getId());

            $paymentData->pricesByCurrencyId[$currency->getId()] = $price;
        }
    }
}
