<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

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
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    protected $swift;

    /**
     * @var \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod")
     * @ORM\JoinColumn(nullable=false, name="payment_method", onDelete="CASCADE")
     */
    protected $goPayPaymentMethod;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $imageNormalUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $imageLargeUrl;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $isOnline;

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
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
     * @param \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwiftData $goPayBankSwiftData
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSwift()
    {
        return $this->swift;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod
     */
    public function getGoPayPaymentMethod()
    {
        return $this->goPayPaymentMethod;
    }

    /**
     * @return string
     */
    public function getImageNormalUrl()
    {
        return $this->imageNormalUrl;
    }

    /**
     * @return string
     */
    public function getImageLargeUrl()
    {
        return $this->imageLargeUrl;
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        return $this->isOnline;
    }
}
