<?php

declare(strict_types=1);

namespace App\Model\GoPay\BankSwift;

use Doctrine\ORM\EntityManagerInterface;

class GoPayBankSwiftFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory
     */
    private $goPayBankSwiftDataFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        GoPayBankSwiftDataFactory $goPayBankSwiftDataFactory
    ) {
        $this->em = $em;
        $this->goPayBankSwiftDataFactory = $goPayBankSwiftDataFactory;
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
    public function setGoPayBankSwiftDataFromSwiftRawData(GoPayBankSwiftData $goPayBankSwiftData, array $swiftRawData): void
    {
        $goPayBankSwiftData->swift = $swiftRawData['swift'];
        $goPayBankSwiftData->name = $swiftRawData['label']['cs']; // GoPay doesn't support Slovak names
        $goPayBankSwiftData->imageNormalUrl = $swiftRawData['image']['normal'];
        $goPayBankSwiftData->imageLargeUrl = $swiftRawData['image']['large'];
        $goPayBankSwiftData->isOnline = (bool)$swiftRawData['isOnline'];
    }
}
