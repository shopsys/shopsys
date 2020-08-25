<?php

declare(strict_types=1);

namespace App\Model\GoPay\BankSwift;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *      name="gopay_bank_swifts",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="gopay_bank_swift_unique", columns={"payment_method", "swift"})
 *      }
 * )
 * @ORM\Entity
 */
class GoPayBankSwift
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $swift;

    /**
     * @var \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     * @ORM\ManyToOne(targetEntity="App\Model\GoPay\PaymentMethod\GoPayPaymentMethod")
     * @ORM\JoinColumn(nullable=false, name="payment_method", onDelete="CASCADE")
     */
    private $goPayPaymentMethod;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $imageNormalUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $imageLargeUrl;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isOnline;

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
     */
    public function __construct(GoPayBankSwiftData $goPayBankSwiftData)
    {
        $this->swift = $goPayBankSwiftData->swift;
        $this->goPayPaymentMethod = $goPayBankSwiftData->goPayPaymentMethod;
        $this->name = $goPayBankSwiftData->name;
        $this->imageNormalUrl = $goPayBankSwiftData->imageNormalUrl;
        $this->imageLargeUrl = $goPayBankSwiftData->imageLargeUrl;
        $this->isOnline = $goPayBankSwiftData->isOnline;
    }

    /**
     * @param \App\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
     */
    public function edit(GoPayBankSwiftData $goPayBankSwiftData): void
    {
        $this->name = $goPayBankSwiftData->name;
        $this->imageNormalUrl = $goPayBankSwiftData->imageNormalUrl;
        $this->imageLargeUrl = $goPayBankSwiftData->imageLargeUrl;
        $this->isOnline = $goPayBankSwiftData->isOnline;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSwift(): string
    {
        return $this->swift;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    public function getGoPayPaymentMethod(): GoPayPaymentMethod
    {
        return $this->goPayPaymentMethod;
    }

    /**
     * @return string
     */
    public function getImageNormalUrl(): string
    {
        return $this->imageNormalUrl;
    }

    /**
     * @return string
     */
    public function getImageLargeUrl(): string
    {
        return $this->imageLargeUrl;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->isOnline;
    }
}
