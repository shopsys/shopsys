<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportDomainNotFoundException;

/**
 * @ORM\Table(name="transports")
 * @ORM\Entity
 */
class Transport extends AbstractTranslatableEntity implements OrderableEntityInterface
{
    private const GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Transport\TransportTranslation")
     */
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDomain[]
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Transport\TransportDomain", mappedBy="transport", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPrice[]
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Transport\TransportPrice", mappedBy="transport", cascade={"persist"})
     */
    protected $prices;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $vat;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @var int
     *
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @var int|null
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment[]|\Doctrine\Common\Collections\Collection
     * @ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment", mappedBy="transports", cascade={"persist"})
     */
    protected $payments;

    public function __construct(TransportData $transportData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->vat = $transportData->vat;
        $this->hidden = $transportData->hidden;
        $this->deleted = false;
        $this->setTranslations($transportData);
        $this->createDomains($transportData);
        $this->prices = new ArrayCollection();
        $this->position = self::GEDMO_SORTABLE_LAST_POSITION;
        $this->payments = new ArrayCollection();
    }

    public function edit(TransportData $transportData): void
    {
        $this->vat = $transportData->vat;
        $this->hidden = $transportData->hidden;
        $this->setTranslations($transportData);
        $this->setDomains($transportData);
    }

    protected function setTranslations(TransportData $transportData): void
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
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(?string $locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->translation($locale)->getDescription();
    }

    public function getInstructions(?string $locale = null): ?string
    {
        return $this->translation($locale)->getInstructions();
    }

    public function isEnabled(int $domainId): bool
    {
        return $this->getTransportDomain($domainId)->isEnabled();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    public function getPrice(Currency $currency): \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
    {
        foreach ($this->prices as $price) {
            if ($price->getCurrency() === $currency) {
                return $price;
            }
        }

        $message = 'Transport price with currency ID ' . $currency->getId()
            . ' from transport with ID ' . $this->getId() . 'not found.';
        throw new \Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException($message);
    }
    
    public function setPrice(
        TransportPriceFactoryInterface $transportPriceFactory,
        Currency $currency,
        string $price
    ): void {
        foreach ($this->prices as $transportInputPrice) {
            if ($transportInputPrice->getCurrency() === $currency) {
                $transportInputPrice->setPrice($price);
                return;
            }
        }

        $this->prices[] = $transportPriceFactory->create($this, $currency, $price);
    }

    public function getVat(): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        return $this->vat;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function markAsDeleted(): void
    {
        $this->deleted = true;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    protected function setDomains(TransportData $transportData): void
    {
        foreach ($this->domains as $transportDomain) {
            $domainId = $transportDomain->getDomainId();
            $transportDomain->setEnabled($transportData->enabled[$domainId]);
        }
    }

    protected function createDomains(TransportData $transportData): void
    {
        $domainIds = array_keys($transportData->enabled);

        foreach ($domainIds as $domainId) {
            $transportDomain = new TransportDomain($this, $domainId);
            $this->domains[] = $transportDomain;
        }

        $this->setDomains($transportData);
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation
    {
        return new TransportTranslation();
    }

    public function addPayment(Payment $payment): void
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->addTransport($this);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     */
    public function setPayments(array $payments): void
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

    public function removePayment(Payment $payment): void
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            $payment->removeTransport($this);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]|\Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    protected function getTransportDomain(int $domainId): \Shopsys\FrameworkBundle\Model\Transport\TransportDomain
    {
        if ($this->domains !== null) {
            foreach ($this->domains as $transportDomain) {
                if ($transportDomain->getDomainId() === $domainId) {
                    return $transportDomain;
                }
            }
        }

        throw new TransportDomainNotFoundException($this->id, $domainId);
    }
}
