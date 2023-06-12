<?php

declare(strict_types=1);

namespace App\Model\GoPay\PaymentMethod;

use App\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory;
use App\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use App\Model\GoPay\BankSwift\GoPayBankSwiftRepository;
use App\Model\GoPay\GoPayClientFactory;
use App\Model\Payment\PaymentFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class GoPayPaymentMethodFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodRepository $goPayPaymentMethodRepository
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftRepository $goPayBankSwiftRepository
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory
     */
    public function __construct(
        private EntityManagerInterface $em,
        private GoPayClientFactory $goPayClientFactory,
        private CurrencyFacade $currencyFacade,
        private GoPayPaymentMethodRepository $goPayPaymentMethodRepository,
        private GoPayBankSwiftFacade $goPayBankSwiftFacade,
        private PaymentFacade $paymentFacade,
        private GoPayBankSwiftRepository $goPayBankSwiftRepository,
        private GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory,
        private GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory,
    ) {
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $paymentMethodData
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    public function create(GoPayPaymentMethodData $paymentMethodData): GoPayPaymentMethod
    {
        $paymentMethod = new GoPayPaymentMethod($paymentMethodData);
        $this->em->persist($paymentMethod);
        $this->em->flush();

        return $paymentMethod;
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    private function edit(GoPayPaymentMethod $goPayPaymentMethod, GoPayPaymentMethodData $goPayPaymentMethodData): void
    {
        $goPayPaymentMethod->edit($goPayPaymentMethodData);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function downloadAndUpdatePaymentMethods(DomainConfig $domainConfig): void
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
        $goPayClient = $this->goPayClientFactory->createByLocale($domainConfig->getLocale());
        $goPayPaymentMethodsRawData = $goPayClient->downloadGoPayPaymentMethodsByCurrency($currency);
        $paymentMethodByIdentifier =
            $this->goPayPaymentMethodRepository->getAllIndexedByIdentifierByCurrencyId($currency->getId());

        foreach ($goPayPaymentMethodsRawData as $goPayPaymentMethodRawData) {
            $paymentIdentifier = $goPayPaymentMethodRawData['paymentInstrument'];

            if (array_key_exists($paymentIdentifier, $paymentMethodByIdentifier)) {
                $paymentMethod = $paymentMethodByIdentifier[$paymentIdentifier];
                $this->editByRawData($paymentMethod, $goPayPaymentMethodRawData, $goPayClient->getLanguage());
                $this->paymentFacade->unHideByGoPayPaymentMethod($paymentMethod);
                unset($paymentMethodByIdentifier[$paymentIdentifier]);
            } else {
                $this->createFromRawData($goPayPaymentMethodRawData, $currency, $goPayClient->getLanguage());
            }
        }

        foreach ($paymentMethodByIdentifier as $paymentMethod) {
            $this->paymentFacade->hideByGoPayPaymentMethod($paymentMethod);
        }
    }

    /**
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAll(): array
    {
        return $this->goPayPaymentMethodRepository->getAll();
    }

    /**
     * @return string[]
     */
    public function getAllTypeIdentifiers(): array
    {
        return $this->goPayPaymentMethodRepository->getAllTypeIdentifiers();
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     * @param array $goPayMethodRawData
     * @param string $language
     */
    public function setFromGoPayRawData(
        GoPayPaymentMethodData $goPayPaymentMethodData,
        array $goPayMethodRawData,
        string $language,
    ): void {
        $goPayPaymentMethodData->name = sprintf('[%s] %s', $language, $goPayMethodRawData['label']['cs']);
        $goPayPaymentMethodData->identifier = $goPayMethodRawData['paymentInstrument'];
        $goPayPaymentMethodData->imageNormalUrl = $goPayMethodRawData['image']['normal'];
        $goPayPaymentMethodData->imageLargeUrl = $goPayMethodRawData['image']['large'];
        $goPayPaymentMethodData->paymentGroup = $goPayMethodRawData['group'];
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param array $goPayMethodRawData
     * @param string $language
     */
    private function editByRawData(
        GoPayPaymentMethod $goPayPaymentMethod,
        array $goPayMethodRawData,
        string $language,
    ): void {
        $paymentMethodData = $this->goPayPaymentMethodDataFactory->createFromGoPayPaymentMethod($goPayPaymentMethod);
        $this->setFromGoPayRawData($paymentMethodData, $goPayMethodRawData, $language);
        $this->edit($goPayPaymentMethod, $paymentMethodData);

        $this->updateSwiftsFromRawData($goPayPaymentMethod, $goPayMethodRawData);
    }

    /**
     * @param array $goPayMethodRawData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param string $language
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    private function createFromRawData(
        array $goPayMethodRawData,
        Currency $currency,
        string $language,
    ): GoPayPaymentMethod {
        $paymentMethodData = $this->goPayPaymentMethodDataFactory->create();
        $paymentMethodData->currency = $currency;

        $this->setFromGoPayRawData($paymentMethodData, $goPayMethodRawData, $language);
        $paymentMethod = $this->create($paymentMethodData);

        $this->updateSwiftsFromRawData($paymentMethod, $goPayMethodRawData);

        return $paymentMethod;
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param array $goPayMethodRawData
     */
    private function updateSwiftsFromRawData(GoPayPaymentMethod $goPayPaymentMethod, array $goPayMethodRawData): void
    {
        $bankSwiftsBySwift = $this->goPayBankSwiftRepository->getAllIndexedBySwiftByPaymentMethod($goPayPaymentMethod);
        $goPayBankSwiftsRawData = $goPayMethodRawData['enabledSwifts'];

        if ($goPayBankSwiftsRawData === null) {
            $goPayBankSwiftsRawData = [];
        }

        foreach ($goPayBankSwiftsRawData as $goPayBankSwiftRawData) {
            $swift = $goPayBankSwiftRawData['swift'];

            if (array_key_exists($swift, $bankSwiftsBySwift)) {
                $this->goPayBankSwiftFacade->edit($bankSwiftsBySwift[$swift], $goPayBankSwiftRawData);
                unset($bankSwiftsBySwift[$swift]);
            } else {
                $goPayBankSwiftData = $this->goPayBankSwiftDataFactory->create();
                $goPayBankSwiftData->goPayPaymentMethod = $goPayPaymentMethod;
                $this->goPayBankSwiftFacade->setGoPayBankSwiftDataFromSwiftRawData($goPayBankSwiftData, $goPayBankSwiftRawData);
                $this->goPayBankSwiftFacade->create($goPayBankSwiftData);
            }
        }

        if (count($bankSwiftsBySwift) === 0) {
            return;
        }

        foreach ($bankSwiftsBySwift as $bankSwift) {
            $this->em->remove($bankSwift);
        }

        $this->em->flush();
    }
}
