<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @method \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'payments')]
#[ORM\Entity]
#[EntityImage]
class Payment extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    protected const int GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation>
     */
    #[Prezent\Translations(targetEntity: PaymentTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice>
     */
    #[ORM\OneToMany(targetEntity: PaymentPrice::class, mappedBy: 'payment', cascade: ['persist'])]
    protected $prices;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\Transport>
     */
    #[ORM\JoinTable(name: 'payments_transports')]
    #[ORM\ManyToMany(targetEntity: Transport::class, inversedBy: 'payments', cascade: ['persist'])]
    protected $transports;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $hidden;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $deleted;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[Gedmo\SortablePosition]
    protected $position;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $czkRounding;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\PaymentDomain>
     */
    #[ORM\OneToMany(targetEntity: PaymentDomain::class, mappedBy: 'payment', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string')]
    protected $type;

    public function __construct(PaymentData $paymentData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->transports = new ArrayCollection();
        $this->deleted = false;
        $this->createDomains($paymentData);
        $this->prices = new ArrayCollection();
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->uuid = $paymentData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($paymentData);
    }

    public function edit(PaymentData $paymentData): void
    {
        $this->setDomains($paymentData);
        $this->setData($paymentData);
    }

    protected function setData(PaymentData $paymentData): void
    {
        $this->hidden = $paymentData->hidden;
        $this->czkRounding = $paymentData->czkRounding;
        $this->type = $paymentData->type;

        if ($paymentData->type !== PaymentTypeEnum::TYPE_GOPAY) {
            $this->resetGopayPaymentMethods();
        } else {
            foreach ($this->domains as $paymentDomain) {
                $paymentDomain->setHiddenByGoPay(!$paymentDomain->getGoPayPaymentMethod()?->isAvailable());
            }
        }

        $this->setTranslations($paymentData);
    }

    public function addTransport(Transport $transport): void
    {
        if (!$this->transports->contains($transport)) {
            $this->transports->add($transport);
            $transport->addPayment($this);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     */
    public function setTransports($transports): void
    {
        foreach ($this->transports as $currentTransport) {
            if (!in_array($currentTransport, $transports, true)) {
                $this->removeTransport($currentTransport);
            }
        }

        foreach ($transports as $newTransport) {
            $this->addTransport($newTransport);
        }
    }

    public function removeTransport(Transport $transport): void
    {
        if ($this->transports->contains($transport)) {
            $this->transports->removeElement($transport);
            $transport->removePayment($this);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getTransports()
    {
        return $this->transports->getValues();
    }

    protected function setTranslations(PaymentData $paymentData): void
    {
        foreach ($paymentData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($paymentData->description as $locale => $description) {
            $this->translation($locale)->setDescription($description);
        }

        foreach ($paymentData->instructions as $locale => $instructions) {
            $this->translation($locale)->setInstructions($instructions);
        }
    }

    public function setPrice(
        Money $price,
        int $domainId,
    ): void {
        foreach ($this->prices as $paymentInputPrice) {
            if ($paymentInputPrice->getDomainId() === $domainId) {
                $paymentInputPrice->setPrice($price);

                return;
            }
        }
    }

    public function hasPriceForDomain(int $domainId): bool
    {
        foreach ($this->prices as $transportInputPrice) {
            if ($transportInputPrice->getDomainId() === $domainId) {
                return true;
            }
        }

        return false;
    }

    public function addPrice(PaymentPrice $paymentPrice): void
    {
        $this->prices->add($paymentPrice);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    #[EntityLogIdentify(EntityLogIdentify::IS_LOCALIZED)]
    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice[]
     */
    public function getPrices()
    {
        return $this->prices->getValues();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getDescription($locale = null)
    {
        return $this->translation($locale)->getDescription();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getInstructions($locale = null)
    {
        return $this->translation($locale)->getInstructions();
    }

    /**
     * @return bool
     */
    public function isEnabled(int $domainId)
    {
        return $this->getPaymentDomain($domainId)->isEnabled();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    public function markAsDeleted(): void
    {
        $this->deleted = true;
        $this->transports->clear();
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    #[Override]
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isCzkRounding()
    {
        return $this->czkRounding;
    }

    public function isGoPay(): bool
    {
        return $this->type === PaymentTypeEnum::TYPE_GOPAY;
    }

    public function isHiddenByGoPayByDomainId(int $domainId): bool
    {
        return $this->getPaymentDomain($domainId)->isHiddenByGoPay();
    }

    public function hideByGoPayOnDomain(int $domainId): void
    {
        $this->getPaymentDomain($domainId)->setHiddenByGoPay(true);
    }

    public function unHideByGoPayOnDomain(int $domainId): void
    {
        $this->getPaymentDomain($domainId)->setHiddenByGoPay(false);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    public function getGoPayPaymentMethodByDomainId(int $domainId)
    {
        return $this->getPaymentDomain($domainId)->getGoPayPaymentMethod();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new PaymentTranslation();
    }

    protected function setDomains(PaymentData $paymentData): void
    {
        foreach ($this->domains as $paymentDomain) {
            $domainId = $paymentDomain->getDomainId();
            $paymentDomain->setEnabled($paymentData->enabled[$domainId]);
            $paymentDomain->setVat($paymentData->vatsIndexedByDomainId[$domainId]);
            $paymentDomain->setGoPayPaymentMethod($paymentData->goPayPaymentMethodByDomainId[$domainId] ?? null);
            $paymentDomain->setHiddenByGoPay($paymentData->hiddenByGoPay[$domainId] ?? false);
            $paymentDomain->setAccountNumber($paymentData->accountNumberByDomainId[$domainId] ?? null);
            $paymentDomain->setIban($paymentData->ibanByDomainId[$domainId] ?? null);
            $paymentDomain->setBicSwift($paymentData->bicSwiftByDomainId[$domainId] ?? null);
        }
    }

    protected function createDomains(PaymentData $paymentData): void
    {
        $domainIds = array_keys($paymentData->enabled);

        foreach ($domainIds as $domainId) {
            $paymentDomain = new PaymentDomain(
                $this,
                $domainId,
                $paymentData->vatsIndexedByDomainId[$domainId],
                $paymentData->goPayPaymentMethodByDomainId[$domainId] ?? null,
                $paymentData->hiddenByGoPay[$domainId] ?? false,
                $paymentData->accountNumberByDomainId[$domainId] ?? null,
                $paymentData->ibanByDomainId[$domainId] ?? null,
                $paymentData->bicSwiftByDomainId[$domainId] ?? null,
            );
            $this->domains->add($paymentDomain);
        }

        $this->setDomains($paymentData);
    }

    protected function resetGopayPaymentMethods(): void
    {
        foreach ($this->domains as $paymentDomain) {
            $paymentDomain->setGoPayPaymentMethod(null);
            $paymentDomain->setHiddenByGoPay(false);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentDomain
     */
    public function getPaymentDomain(int $domainId)
    {
        foreach ($this->domains as $paymentDomain) {
            if ($paymentDomain->getDomainId() === $domainId) {
                return $paymentDomain;
            }
        }

        throw new PaymentDomainNotFoundException($domainId, $this->id);
    }

    public function getPrice(int $domainId): PaymentPrice
    {
        foreach ($this->prices as $price) {
            if ($price->getDomainId() === $domainId) {
                return $price;
            }
        }

        $message = 'Payment price for domain ID ' . $domainId . ' and payment ID ' . $this->getId() . 'not found.';

        throw new PaymentPriceNotFoundException($message);
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string[]
     */
    protected function getGatewayPayments(): array
    {
        return [
            PaymentTypeEnum::TYPE_GOPAY,
        ];
    }

    public function isGatewayPayment(): bool
    {
        return in_array($this->type, $this->getGatewayPayments(), true);
    }

    public function isOnlinePayment(): bool
    {
        return $this->isGatewayPayment() || $this->type === PaymentTypeEnum::TYPE_BANK_TRANSFER;
    }

    public function getVatForDomain(int $domainId): Vat
    {
        return $this->getPaymentDomain($domainId)->getVat();
    }

    /**
     * @return string|null
     */
    public function getAccountNumber(int $domainId)
    {
        return $this->getPaymentDomain($domainId)->getAccountNumber();
    }

    /**
     * @return string|null
     */
    public function getIban(int $domainId)
    {
        return $this->getPaymentDomain($domainId)->getIban();
    }

    /**
     * @return string|null
     */
    public function getBicSwift(int $domainId)
    {
        return $this->getPaymentDomain($domainId)->getBicSwift();
    }
}
