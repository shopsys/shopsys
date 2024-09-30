<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation translation(?string $locale = null)
 */
class Transport extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    protected const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Transport\TransportTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\TransportDomain>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Transport\TransportDomain", mappedBy="transport", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\TransportPrice>
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Transport\TransportPrice", mappedBy="transport", cascade={"persist"})
     */
    protected $prices;

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
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Payment\Payment>
     * @ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment", mappedBy="transports", cascade={"persist"})
     */
    protected $payments;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $daysUntilDelivery;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $trackingUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=25)
     */
    protected $type;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    public function __construct(TransportData $transportData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->createDomains($transportData);
        $this->deleted = false;
        $this->prices = new ArrayCollection();
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->payments = new ArrayCollection();
        $this->uuid = $transportData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($transportData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    public function edit(TransportData $transportData)
    {
        $this->setDomains($transportData);
        $this->setData($transportData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    protected function setData(TransportData $transportData): void
    {
        $this->hidden = $transportData->hidden;
        $this->daysUntilDelivery = $transportData->daysUntilDelivery;
        $this->type = $transportData->type;
        $this->trackingUrl = $transportData->trackingUrl;
        $this->setTranslations($transportData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    protected function setTranslations(TransportData $transportData)
    {
        foreach ($transportData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($transportData->description as $locale => $description) {
            $this->translation($locale)->setDescription($description);
        }

        foreach ($transportData->instructions as $locale => $instructions) {
            $this->translation($locale)->setInstructions($instructions);
        }

        foreach ($transportData->trackingInstructions as $locale => $trackingInstruction) {
            $this->translation($locale)->setTrackingInstruction($trackingInstruction);
        }
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
        return $this->getTransportDomain($domainId)->isEnabled();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice[]
     */
    public function getPrices()
    {
        return $this->prices->getValues();
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Transport\TransportPrice> $prices
     */
    public function setPrices($prices)
    {
        $this->prices = new ArrayCollection($prices);
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    protected function setDomains(TransportData $transportData)
    {
        foreach ($this->domains as $transportDomain) {
            $domainId = $transportDomain->getDomainId();
            $transportDomain->setEnabled($transportData->enabled[$domainId]);
            $transportDomain->setVat($transportData->inputPricesByDomain[$domainId]->vat);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    protected function createDomains(TransportData $transportData)
    {
        $domainIds = array_keys($transportData->enabled);

        foreach ($domainIds as $domainId) {
            $transportDomain = new TransportDomain($this, $domainId, $transportData->inputPricesByDomain[$domainId]->vat);
            $this->domains->add($transportDomain);
        }

        $this->setDomains($transportData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation
     */
    protected function createTranslation()
    {
        return new TransportTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    public function addPayment(Payment $payment)
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->addTransport($this);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     */
    public function setPayments(array $payments)
    {
        foreach ($this->payments as $currentPayment) {
            if (!in_array($currentPayment, $payments, true)) {
                $this->removePayment($currentPayment);
            }
        }

        foreach ($payments as $newPayment) {
            $this->addPayment($newPayment);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    public function removePayment(Payment $payment)
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            $payment->removeTransport($this);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getPayments()
    {
        return $this->payments->getValues();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportDomain
     */
    public function getTransportDomain(int $domainId)
    {
        foreach ($this->domains as $transportDomain) {
            if ($transportDomain->getDomainId() === $domainId) {
                return $transportDomain;
            }
        }

        throw new TransportDomainNotFoundException($domainId, $this->id);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
    public function getLowestPriceOnDomain(int $domainId): TransportPrice
    {
        $lowestPrice = null;

        foreach ($this->getPricesByDomainId($domainId) as $transportPrice) {
            if ($lowestPrice === null || $transportPrice->getPrice()->isLessThanOrEqualTo($lowestPrice->getPrice())) {
                $lowestPrice = $transportPrice;
            }
        }

        if ($lowestPrice === null) {
            $message = 'Transport price with domain ID ' . $domainId . ' and transport ID ' . $this->getId() . ' not found.';

            throw new TransportPriceNotFoundException($message);
        }

        return $lowestPrice;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice[]
     */
    public function getPricesByDomainId(int $domainId): array
    {
        $prices = array_filter($this->prices->toArray(), static fn (TransportPrice $price) => $price->getDomainId() === $domainId);

        if ($prices !== []) {
            return $prices;
        }

        $message = 'Transport prices with domain ID ' . $domainId . ' and transport ID ' . $this->getId() . ' not found.';

        throw new TransportPriceNotFoundException($message);
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getDaysUntilDelivery()
    {
        return $this->daysUntilDelivery;
    }

    /**
     * @return bool
     */
    public function isPersonalPickup(): bool
    {
        return $this->type === TransportTypeEnum::TYPE_PERSONAL_PICKUP;
    }

    /**
     * @return bool
     */
    public function isPacketery(): bool
    {
        return $this->type === TransportTypeEnum::TYPE_PACKETERY;
    }

    /**
     * @return ?string
     */
    public function getTrackingUrl()
    {
        return $this->trackingUrl;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getTrackingInstruction($locale = null)
    {
        return $this->translation($locale)->getTrackingInstruction();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
