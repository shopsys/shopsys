<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class GoPayBankSwiftFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory,
        protected readonly GoPayPaymentMethodRepository $goPayPaymentMethodRepository,
        protected readonly GoPayBankSwiftFactory $goPayBankSwiftFactory,
        protected readonly GoPayBankSwiftRepository $goPayBankSwiftRepository,
    ) {
    }

    public function create(GoPayBankSwiftData $goPayBankSwiftData): GoPayBankSwift
    {
        $bankSwift = $this->goPayBankSwiftFactory->create($goPayBankSwiftData);
        $this->em->persist($bankSwift);
        $this->em->flush();

        return $bankSwift;
    }

    public function edit(GoPayBankSwift $goPayBankSwift, array $swiftRawData): void
    {
        $goPayBankSwiftData = $this->goPayBankSwiftDataFactory->createFromGoPayBankSwift($goPayBankSwift);
        $this->setGoPayBankSwiftDataFromSwiftRawData($goPayBankSwiftData, $swiftRawData);

        $goPayBankSwift->edit($goPayBankSwiftData);
        $this->em->flush();
    }

    public function setGoPayBankSwiftDataFromSwiftRawData(
        GoPayBankSwiftData $goPayBankSwiftData,
        array $swiftRawData,
    ): void {
        $goPayBankSwiftData->swift = $swiftRawData['swift'];
        $goPayBankSwiftData->name = $swiftRawData['label']['cs']; // GoPay doesn't support Slovak names
        $goPayBankSwiftData->imageNormalUrl = $swiftRawData['image']['normal'];
        $goPayBankSwiftData->imageLargeUrl = $swiftRawData['image']['large'];
        $goPayBankSwiftData->isOnline = (bool)$swiftRawData['isOnline'];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function getAllByCurrencyId(int $currencyId): array
    {
        return $this->goPayPaymentMethodRepository->getBankSwiftsByCurrencyId($currencyId);
    }

    public function findBySwiftAndPaymentMethodAndCurrency(
        string $goPayBankSwift,
        GoPayPaymentMethod $goPayPaymentMethod,
        Currency $currency,
    ): ?GoPayBankSwift {
        return $this->goPayBankSwiftRepository->findBySwiftAndPaymentMethodAndCurrency($goPayBankSwift, $goPayPaymentMethod, $currency);
    }
}
