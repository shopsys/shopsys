<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class GoPayDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PAYMENT_CARD_METHOD = 'gopay_payment_card_method';
    public const BANK_ACCOUNT_METHOD = 'gopay_bank_account_method';

    public const AIRBANK_SWIFT_PATTERN = 'AIRA%sPP';
    public const FIO_SWIFT_PATTERN = 'FIOB%sPP';

    private const SWIFT_DEMO_DATA = [
        [
            'name' => 'Airbank',
            'swift_pattern' => self::AIRBANK_SWIFT_PATTERN,
            'image_normal_url' => 'airbank image Url',
            'image_large_url' => 'airbank large image Url',
            'is_online' => true,
        ],
        [
            'name' => 'FIO bank',
            'swift_pattern' => self::FIO_SWIFT_PATTERN,
            'image_normal_url' => 'FIO bank image Url',
            'image_large_url' => 'FIO bank large image Url',
            'is_online' => true,
        ],
    ];

    private const DEMO_DATA = [
        [
            'reference_name' => self::PAYMENT_CARD_METHOD,
            'identifier' => GoPayPaymentMethod::IDENTIFIER_PAYMENT_CARD,
            'name_pattern' => '[%s] Credit card',
            'image_normal_url' => 'payment_card',
            'image_large_url' => 'payment_card@2x',
            'payment_group' => 'card-payment',
        ],
        [
            'reference_name' => self::BANK_ACCOUNT_METHOD,
            'identifier' => GoPayPaymentMethod::IDENTIFIER_BANK_TRANSFER,
            'name_pattern' => '[%s] Quick bank account transfer',
            'image_normal_url' => 'bank_account',
            'image_large_url' => 'bank_account@2x',
            'payment_group' => 'bank-transfer',
        ],
        [
            'reference_name' => null,
            'identifier' => 'GOPAY',
            'name_pattern' => '[%s] GoPay',
            'image_normal_url' => 'gopay',
            'image_large_url' => 'gopay@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'identifier' => 'PAYPAL',
            'name_pattern' => '[%s] PayPal',
            'image_normal_url' => 'paypal',
            'image_large_url' => 'paypal@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'identifier' => 'BITCOIN',
            'name_pattern' => '[%s] Bitcoin',
            'image_normal_url' => 'bitcoin',
            'image_large_url' => 'bitcoin@2x',
            'payment_group' => 'wallet',
        ],
        [
            'reference_name' => null,
            'identifier' => 'PRSMS',
            'name_pattern' => '[%s] Premium SMS',
            'image_normal_url' => 'prsms',
            'image_large_url' => 'prsms@2x',
            'payment_group' => 'others',
        ],
        [
            'reference_name' => null,
            'identifier' => 'MPAYMENT',
            'name_pattern' => '[%s] m-payment',
            'image_normal_url' => 'mpayment',
            'image_large_url' => 'mpayment@2x',
            'payment_group' => 'others',
        ],
        [
            'reference_name' => null,
            'identifier' => 'PAYSAFECARD',
            'name_pattern' => '[%s] Paysafecard',
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        private readonly GoPayPaymentMethodFacade $goPayPaymentMethodFacade,
        private readonly GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory,
        private readonly GoPayBankSwiftFacade $goPayBankSwiftFacade,
        private readonly GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory,
        private readonly Domain $domain,
        private readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            foreach (self::DEMO_DATA as $data) {
                $locale = $domainConfig->getLocale();
                $domainId = $domainConfig->getId();

                $goPayPaymentMethodData = $this->getGoPayPaymentMethodData($data, $locale, $domainId);
                $goPayPaymentMethod = $this->createGoPayPaymentMethod($data['reference_name'], $goPayPaymentMethodData, $domainId);

                if ($data['identifier'] !== 'BANK_ACCOUNT') {
                    continue;
                }

                foreach (self::SWIFT_DEMO_DATA as $swiftData) {
                    $this->createGoPayBankSwift($goPayPaymentMethod, $swiftData, $locale);
                }
            }
        }
    }

    /**
     * @param array $data
     * @param string $locale
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData
     */
    private function getGoPayPaymentMethodData(
        array $data,
        string $locale,
        int $domainId,
    ): GoPayPaymentMethodData {
        $goPayPaymentMethodData = $this->goPayPaymentMethodDataFactory->createInstance();
        $goPayPaymentMethodData->identifier = $data['identifier'];
        $goPayPaymentMethodData->name = sprintf($data['name_pattern'], $locale);
        $goPayPaymentMethodData->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $goPayPaymentMethodData->imageNormalUrl = 'https://gate.gopay.cz/images/checkout/' . $data['image_normal_url'] . '.png';
        $goPayPaymentMethodData->imageLargeUrl = 'https://gate.gopay.cz/images/checkout/' . $data['image_large_url'] . '.png';
        $goPayPaymentMethodData->paymentGroup = $data['payment_group'];

        return $goPayPaymentMethodData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param array $swiftData
     * @param string $locale
     */
    private function createGoPayBankSwift(
        GoPayPaymentMethod $goPayPaymentMethod,
        array $swiftData,
        string $locale,
    ): void {
        $goPayBankSwiftData = $this->goPayBankSwiftDataFactory->createInstance();
        $goPayBankSwiftData->goPayPaymentMethod = $goPayPaymentMethod;
        $goPayBankSwiftData->name = $swiftData['name'];
        $goPayBankSwiftData->swift = sprintf($swiftData['swift_pattern'], $locale);
        $goPayBankSwiftData->isOnline = $swiftData['is_online'];
        $goPayBankSwiftData->imageNormalUrl = $swiftData['image_normal_url'];
        $goPayBankSwiftData->imageLargeUrl = $swiftData['image_large_url'];
        $this->goPayBankSwiftFacade->create($goPayBankSwiftData);
    }

    /**
     * @param string|null $referenceName
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    private function createGoPayPaymentMethod(
        ?string $referenceName,
        GoPayPaymentMethodData $goPayPaymentMethodData,
        int $domainId,
    ): GoPayPaymentMethod {
        $goPayPaymentMethod = $this->goPayPaymentMethodFacade->create($goPayPaymentMethodData);

        if ($referenceName !== null) {
            $this->addReferenceForDomain($referenceName, $goPayPaymentMethod, $domainId);
        }

        return $goPayPaymentMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CurrencyDataFixture::class,
        ];
    }
}
