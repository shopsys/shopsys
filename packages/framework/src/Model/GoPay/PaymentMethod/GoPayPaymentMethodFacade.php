<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftRepository;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class GoPayPaymentMethodFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\GoPay\GoPayClientFactory $goPayClientFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodRepository $goPayPaymentMethodRepository
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftFacade $goPayBankSwiftFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftRepository $goPayBankSwiftRepository
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodFactory $goPayPaymentMethodFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GoPayClientFactory $goPayClientFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly GoPayPaymentMethodRepository $goPayPaymentMethodRepository,
        protected readonly GoPayBankSwiftFacade $goPayBankSwiftFacade,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly GoPayBankSwiftRepository $goPayBankSwiftRepository,
        protected readonly GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory,
        protected readonly GoPayPaymentMethodDataFactory $goPayPaymentMethodDataFactory,
        protected readonly GoPayPaymentMethodFactory $goPayPaymentMethodFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $paymentMethodData
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    public function create(GoPayPaymentMethodData $paymentMethodData): GoPayPaymentMethod
    {
        $paymentMethod = $this->goPayPaymentMethodFactory->create($paymentMethodData);
        $this->em->persist($paymentMethod);
        $this->em->flush();

        return $paymentMethod;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    protected function edit(
        GoPayPaymentMethod $goPayPaymentMethod,
        GoPayPaymentMethodData $goPayPaymentMethodData,
    ): void {
        $goPayPaymentMethod->edit($goPayPaymentMethodData);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function downloadAndUpdatePaymentMethods(DomainConfig $domainConfig): void
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainConfig->getId());
        $goPayClient = $this->goPayClientFactory->createByDomain($domainConfig);
        $goPayPaymentMethodsRawData = $goPayClient->downloadGoPayPaymentMethodsByCurrency($currency);
        $paymentMethodByIdentifier = $this->goPayPaymentMethodRepository->getAllIndexedByIdentifierByDomainId($domainConfig->getId());

        foreach ($goPayPaymentMethodsRawData as $goPayPaymentMethodRawData) {
            $paymentIdentifier = $goPayPaymentMethodRawData['paymentInstrument'];

            if (array_key_exists($paymentIdentifier, $paymentMethodByIdentifier)) {
                $paymentMethod = $paymentMethodByIdentifier[$paymentIdentifier];
                $this->editByRawData($paymentMethod, $goPayPaymentMethodRawData, $goPayClient->getLanguage());
                $this->paymentFacade->unHideByGoPayPaymentMethod($paymentMethod, $domainConfig->getId());
                unset($paymentMethodByIdentifier[$paymentIdentifier]);
            } else {
                $this->createFromRawData(
                    $goPayPaymentMethodRawData,
                    $currency,
                    $goPayClient->getLanguage(),
                    $domainConfig->getId(),
                );
            }
        }

        foreach ($paymentMethodByIdentifier as $paymentMethod) {
            $paymentMethod->setUnavailable();
            $this->paymentFacade->hideByGoPayPaymentMethod($paymentMethod, $domainConfig->getId());
        }

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
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
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
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
        $goPayPaymentMethodData->available = true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param array $goPayMethodRawData
     * @param string $language
     */
    protected function editByRawData(
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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    protected function createFromRawData(
        array $goPayMethodRawData,
        Currency $currency,
        string $language,
        int $domainId,
    ): GoPayPaymentMethod {
        $paymentMethodData = $this->goPayPaymentMethodDataFactory->createInstance();
        $paymentMethodData->currency = $currency;
        $paymentMethodData->domainId = $domainId;

        $this->setFromGoPayRawData($paymentMethodData, $goPayMethodRawData, $language);
        $paymentMethod = $this->create($paymentMethodData);

        $this->updateSwiftsFromRawData($paymentMethod, $goPayMethodRawData);

        return $paymentMethod;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param array $goPayMethodRawData
     */
    protected function updateSwiftsFromRawData(GoPayPaymentMethod $goPayPaymentMethod, array $goPayMethodRawData): void
    {
        $bankSwiftsBySwift = $this->goPayBankSwiftRepository->getAllIndexedBySwiftByPaymentMethod($goPayPaymentMethod);
        $goPayBankSwiftsRawData = $goPayMethodRawData['enabledSwifts'] ?? null;

        if ($goPayBankSwiftsRawData === null) {
            $goPayBankSwiftsRawData = [];
        }

        foreach ($goPayBankSwiftsRawData as $goPayBankSwiftRawData) {
            $swift = $goPayBankSwiftRawData['swift'];

            if (array_key_exists($swift, $bankSwiftsBySwift)) {
                $this->goPayBankSwiftFacade->edit($bankSwiftsBySwift[$swift], $goPayBankSwiftRawData);
                unset($bankSwiftsBySwift[$swift]);
            } else {
                $goPayBankSwiftData = $this->goPayBankSwiftDataFactory->createInstance();
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
