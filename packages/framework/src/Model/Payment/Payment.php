<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation translation(?string $locale = null)
 */
class Payment extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    protected const GEDMO_SORTABLE_LAST_POSITION = -1;
    public const TYPE_GOPAY = 'goPay';
    public const TYPE_BASIC = 'basic';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\PaymentPrice", mappedBy="payment", cascade={"persist"})
     */
    protected $prices;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\Transport>
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport", inversedBy="payments", cascade={"persist"})
     * @ORM\JoinTable(name="payments_transports")
     */
    protected $transports;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @var int|null
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $czkRounding;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\PaymentDomain>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\PaymentDomain", mappedBy="payment", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $goPayPaymentMethod;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hiddenByGoPay;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    public function edit(PaymentData $paymentData)
    {
        $this->setDomains($paymentData);
        $this->setData($paymentData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function setData(PaymentData $paymentData): void
    {
        $this->hidden = $paymentData->hidden;
        $this->czkRounding = $paymentData->czkRounding;
        $this->type = $paymentData->type;
        $this->setGoPayPaymentMethod($paymentData);
        $this->hiddenByGoPay = $paymentData->hiddenByGoPay;
        $this->setTranslations($paymentData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     */
    public function addTransport(Transport $transport)
    {
        if (!$this->transports->contains($transport)) {
            $this->transports->add($transport);
            $transport->addPayment($this);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     */
    public function setTransports($transports)
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     */
    public function removeTransport(Transport $transport)
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function setTranslations(PaymentData $paymentData)
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     */
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

    /**
     * @param int $domainId
     * @return bool
     */
    public function hasPriceForDomain(int $domainId): bool
    {
        foreach ($this->prices as $transportInputPrice) {
            if ($transportInputPrice->getDomainId() === $domainId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice $paymentPrice
     */
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
     * @param int $domainId
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

    public function markAsDeleted()
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
    public function setPosition($position)
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

    /**
     * @return bool
     */
    public function isGoPay(): bool
    {
        return $this->type === self::TYPE_GOPAY;
    }

    /**
     * @return bool
     */
    public function isHiddenByGoPay()
    {
        return $this->hiddenByGoPay;
    }

    public function hideByGoPay(): void
    {
        $this->hiddenByGoPay = true;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    public function getGoPayPaymentMethod()
    {
        return $this->goPayPaymentMethod;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function unHideByGoPay(): void
    {
        $this->hiddenByGoPay = false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function setGoPayPaymentMethod(PaymentData $paymentData): void
    {
        $this->goPayPaymentMethod = null;

        if ($this->type === self::TYPE_GOPAY) {
            $this->goPayPaymentMethod = $paymentData->goPayPaymentMethod;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation
     */
    protected function createTranslation()
    {
        return new PaymentTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function setDomains(PaymentData $paymentData)
    {
        foreach ($this->domains as $paymentDomain) {
            $domainId = $paymentDomain->getDomainId();
            $paymentDomain->setEnabled($paymentData->enabled[$domainId]);
            $paymentDomain->setVat($paymentData->vatsIndexedByDomainId[$domainId]);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function createDomains(PaymentData $paymentData)
    {
        $domainIds = array_keys($paymentData->enabled);

        foreach ($domainIds as $domainId) {
            $paymentDomain = new PaymentDomain($this, $domainId, $paymentData->vatsIndexedByDomainId[$domainId]);
            $this->domains->add($paymentDomain);
        }

        $this->setDomains($paymentData);
    }

    /**
     * @param int $domainId
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

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice
     */
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
}
