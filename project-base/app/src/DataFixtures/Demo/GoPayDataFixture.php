<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;

class GoPayDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PAYMENT_CARD_METHOD = 'gopay_payment_card_method';
    public const BANK_ACCOUNT_METHOD = 'gopay_bank_account_method';

    private const SWIFT_DEMO_DATA = [
        [
            'name' => 'Airbank',
            'swift' => '123456XZY',
            'image_normal_url' => 'airbank image Url',
            'image_large_url' => 'airbank large image Url',
            'is_online' => true,
        ],
        [
            'name' => 'Aqua bank',
            'swift' => 'ABC123456',
            'image_normal_url' => 'airbank image Url',
            'image_large_url' => 'airbank large image Url',
            'is_online' => true,
        ],
    ];

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
            'reference_name' => self::BANK_ACCOUNT_METHOD,
            'currency' => CurrencyDataFixture::CURRENCY_CZK,
            'identifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
            'name' => '[CS] Rychlý bankovní převod',
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
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade $goPayPaymentMethodFacade
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory
     */
    public function __construct(
        private GoPayPaymentMethodFacade $goPayPaymentMethodFacade,
        private GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory,
        private GoPayBankSwiftFacade $goPayBankSwiftFacade,
        private GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::DEMO_DATA as $data) {
            $goPayPaymentMethodData = $this->goPayPaymentMethodDataFactory->createInstance();
            $goPayPaymentMethodData->identifier = $data['identifier'];
            $goPayPaymentMethodData->name = $data['name'];
            $goPayPaymentMethodData->currency = $this->getReference($data['currency']);
            $goPayPaymentMethodData->imageNormalUrl = 'https://gate.gopay.cz/images/checkout/' . $data['image_normal_url'] . '.png';
            $goPayPaymentMethodData->imageLargeUrl = 'https://gate.gopay.cz/images/checkout/' . $data['image_large_url'] . '.png';
            $goPayPaymentMethodData->paymentGroup = $data['payment_group'];
            $goPayPaymentMethod = $this->createGoPayPaymentMethod($data['reference_name'], $goPayPaymentMethodData);

            if ($data['identifier'] !== 'BANK_ACCOUNT') {
                continue;
            }

            foreach (self::SWIFT_DEMO_DATA as $swiftData) {
                $goPayBankSwiftData = $this->goPayBankSwiftDataFactory->createInstance();
                $goPayBankSwiftData->goPayPaymentMethod = $goPayPaymentMethod;
                $goPayBankSwiftData->name = $swiftData['name'];
                $goPayBankSwiftData->swift = $swiftData['swift'];
                $goPayBankSwiftData->isOnline = $swiftData['is_online'];
                $goPayBankSwiftData->imageNormalUrl = $swiftData['image_normal_url'];
                $goPayBankSwiftData->imageLargeUrl = $swiftData['image_large_url'];
                $this->goPayBankSwiftFacade->create($goPayBankSwiftData);
            }
        }
    }

    /**
     * @param string|null $referenceName
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    private function createGoPayPaymentMethod(
        ?string $referenceName,
        GoPayPaymentMethodData $goPayPaymentMethodData,
    ) {
        $goPayPaymentMethod = $this->goPayPaymentMethodFacade->create($goPayPaymentMethodData);

        if ($referenceName !== null) {
            $this->addReference($referenceName, $goPayPaymentMethod);
        }

        return $goPayPaymentMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            CurrencyDataFixture::class,
        ];
    }
}
