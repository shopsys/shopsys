<?php

declare(strict_types=1);

namespace App\Model\GoPay\BankSwift;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;

class GoPayBankSwiftFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodRepository $goPayPaymentMethodRepository
     */
    public function __construct(
        private EntityManagerInterface $em,
        private GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory,
        private GoPayPaymentMethodRepository $goPayPaymentMethodRepository,
    ) {
    }

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwift
     */
    public function create(GoPayBankSwiftData $goPayBankSwiftData): GoPayBankSwift
    {
        $bankSwift = new GoPayBankSwift($goPayBankSwiftData);
        $this->em->persist($bankSwift);
        $this->em->flush();

        return $bankSwift;
    }

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwift $goPayBankSwift
     * @param array $swiftRawData
     */
    public function edit(GoPayBankSwift $goPayBankSwift, array $swiftRawData): void
    {
        $goPayBankSwiftData = $this->goPayBankSwiftDataFactory->createFromGoPayBankSwift($goPayBankSwift);
        $this->setGoPayBankSwiftDataFromSwiftRawData($goPayBankSwiftData, $swiftRawData);

        $goPayBankSwift->edit($goPayBankSwiftData);
        $this->em->flush();
    }

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
     * @param array $swiftRawData
     */
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
     * @param int $currencyId
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function getAllByCurrencyId(int $currencyId): array
    {
        return $this->goPayPaymentMethodRepository->getBankSwiftsByCurrencyId($currencyId);
    }
}
