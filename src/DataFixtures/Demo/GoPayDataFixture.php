<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class GoPayDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PAYMENT_CARD_METHOD = 'gopay_payment_card_method';

    private const DEMO_DATA = [
        [
            'reference_name' => self::PAYMENT_CARD_METHOD,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => GoPayPaymentMethod::IDENTIFIER_PAYMENT_CARD,
            'name' => '[CS] Platební karta',
            'image_normal_url' => 'payment_card',
            'image_large_url' => 'payment_card@2x',
            'payment_group' => 'card-payment',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'BANK_ACCOUNT',
            'name' => '[CS] Rychlý bankovní převod	',
            'image_normal_url' => 'bank_account',
            'image_large_url' => 'bank_account@2x',
            'payment_group' => 'bank-transfer',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'GOPAY',
            'name' => '[CS] GoPay',
            'image_normal_url' => 'gopay',
            'image_large_url' => 'gopay@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'PAYPAL',
            'name' => '[CS] PayPal',
            'image_normal_url' => 'paypal',
            'image_large_url' => 'paypal@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'BITCOIN',
            'name' => '[CS] Bitcoin',
            'image_normal_url' => 'bitcoin',
            'image_large_url' => 'bitcoin@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'PRSMS',
            'name' => '[CS] Premium SMS',
            'image_normal_url' => 'prsms',
            'image_large_url' => 'prsms@2x',
            'payment_group' => 'others',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'MPAYMENT',
            'name' => '[CS] m-platba',
            'image_normal_url' => 'mpayment',
            'image_large_url' => 'mpayment@2x',
            'payment_group' => 'others',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => 'PAYSAFECARD',
            'name' => '[CS] Paysafecard',
            'image_normal_url' => 'paysafecard',
            'image_large_url' => 'paysafecard@2x',
            'payment_group' => 'others',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'PAYMENT_CARD',
            'name' => '[SK] Platební karta',
            'image_normal_url' => 'payment_card',
            'image_large_url' => 'payment_card@2x',
            'payment_group' => 'card-payment',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'BANK_ACCOUNT',
            'name' => '[SK] Rychlý bankovní převod',
            'image_normal_url' => 'bank_account',
            'image_large_url' => 'bank_account@2x',
            'payment_group' => 'bank-transfer',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'GOPAY',
            'name' => '[SK] GoPay',
            'image_normal_url' => 'gopay',
            'image_large_url' => 'gopay@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'PAYPAL',
            'name' => '[SK] PayPal',
            'image_normal_url' => 'paypal',
            'image_large_url' => 'paypal@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'BITCOIN',
            'name' => '[SK] Bitcoin',
            'image_normal_url' => 'bitcoin',
            'image_large_url' => 'bitcoin@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'PRSMS',
            'name' => '[SK] Premium SMS',
            'image_normal_url' => 'prsms',
            'image_large_url' => 'prsms@2x',
            'payment_group' => 'others',
        ],
        [
            'reference_name' => null,
            'currency' => CurrencyDataFixture::CURRENCY_EUR,
            'identifier' => 'PAYSAFECARD',
            'name' => '[SK] Paysafecard',
            'image_normal_url' => 'paysafecard',
            'image_large_url' => 'paysafecard@2x',
            'payment_group' => 'others',
        ],
    ];

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade
     */
    private $goPayPaymentMethodFacade;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory
     */
    private $goPayPaymentMethodDataFactory;

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory
     */
    public function __construct(
        GoPayPaymentMethodFacade $goPayPaymentMethodFacade,
        GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory
    ) {
        $this->goPayPaymentMethodFacade = $goPayPaymentMethodFacade;
        $this->goPayPaymentMethodDataFactory = $goPayPaymentMethodDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DEMO_DATA as $data) {
            $goPayPaymentMethodData = $this->goPayPaymentMethodDataFactory->create();
            $goPayPaymentMethodData->identifier = $data['identifier'];
            $goPayPaymentMethodData->name = $data['name'];
            $goPayPaymentMethodData->currency = $this->getReference($data['currency']);
            $goPayPaymentMethodData->imageNormalUrl = 'https://gate.gopay.cz/images/checkout/' . $data['image_normal_url'] . '.png';
            $goPayPaymentMethodData->imageLargeUrl = 'https://gate.gopay.cz/images/checkout/' . $data['image_large_url'] . '.png';
            $goPayPaymentMethodData->paymentGroup = $data['payment_group'];
            $this->createGoPayPaymentMethod($data['reference_name'], $goPayPaymentMethodData);
        }
    }

    /**
     * @param string|null $referenceName
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    private function createGoPayPaymentMethod(
        ?string $referenceName,
        GoPayPaymentMethodData $goPayPaymentMethodData
    ) {
        $goPayPaymentMethod = $this->goPayPaymentMethodFacade->create($goPayPaymentMethodData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $goPayPaymentMethod);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            CurrencyDataFixture::class,
        ];
    }
}
