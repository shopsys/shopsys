<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @ORM\Table(name="payments")
 * @ORM\Entity
 */
class Payment extends AbstractTranslatableEntity implements OrderableEntityInterface
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
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation")
     */
    protected $translations;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice[]
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\PaymentPrice", mappedBy="payment", cascade={"persist"})
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
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport", inversedBy="payments", cascade={"persist"})
     * @ORM\JoinTable(name="payments_transports")
     */
    protected $transports;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    /**
     * @var bool
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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $czkRounding;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDomain[]|\Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopsys\FrameworkBundle\Model\Payment\PaymentDomain", mappedBy="payment", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $domains;

    public function __construct(PaymentData $paymentData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->vat = $paymentData->vat;
        $this->transports = new ArrayCollection();
        $this->hidden = $paymentData->hidden;
        $this->deleted = false;
        $this->setTranslations($paymentData);
        $this->createDomains($paymentData);
        $this->prices = new ArrayCollection();
        $this->czkRounding = $paymentData->czkRounding;
        $this->position = self::GEDMO_SORTABLE_LAST_POSITION;
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
    public function setTransports(array $transports): void
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
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]|\Doctrine\Common\Collections\Collection
     */
    public function getTransports()
    {
        return $this->transports;
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

    public function edit(PaymentData $paymentData): void
    {
        $this->vat = $paymentData->vat;
        $this->hidden = $paymentData->hidden;
        $this->czkRounding = $paymentData->czkRounding;
        $this->setTranslations($paymentData);
        $this->setDomains($paymentData);
    }
    
    public function setPrice(
        PaymentPriceFactoryInterface $paymentPriceFactory,
        Currency $currency,
        string $price
    ): void {
        foreach ($this->prices as $paymentInputPrice) {
            if ($paymentInputPrice->getCurrency() === $currency) {
                $paymentInputPrice->setPrice($price);
                return;
            }
        }

        $this->prices[] = $paymentPriceFactory->create($this, $currency, $price);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(?string $locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice[]
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    public function getPrice(Currency $currency): \Shopsys\FrameworkBundle\Model\Payment\PaymentPrice
    {
        foreach ($this->prices as $price) {
            if ($price->getCurrency() === $currency) {
                return $price;
            }
        }

        $message = 'Payment price with currency ID ' . $currency->getId() . ' from payment with ID ' . $this->getId() . 'not found.';
        throw new \Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentPriceNotFoundException($message);
    }

    public function getVat(): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        return $this->vat;
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
        return $this->getPaymentDomain($domainId)->isEnabled();
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
        $this->transports->clear();
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
    
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isCzkRounding(): bool
    {
        return $this->czkRounding;
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation
    {
        return new PaymentTranslation();
    }

    protected function setDomains(PaymentData $paymentData): void
    {
        foreach ($this->domains as $paymentDomain) {
            $domainId = $paymentDomain->getDomainId();
            $paymentDomain->setEnabled($paymentData->enabled[$domainId]);
        }
    }

    protected function createDomains(PaymentData $paymentData): void
    {
        $domainIds = array_keys($paymentData->enabled);

        foreach ($domainIds as $domainId) {
            $paymentDomain = new PaymentDomain($this, $domainId);
            $this->domains[] = $paymentDomain;
        }

        $this->setDomains($paymentData);
    }

    protected function getPaymentDomain(int $domainId): \Shopsys\FrameworkBundle\Model\Payment\PaymentDomain
    {
        if ($this->domains !== null) {
            foreach ($this->domains as $paymentDomain) {
                if ($paymentDomain->getDomainId() === $domainId) {
                    return $paymentDomain;
                }
            }
        }

        throw new PaymentDomainNotFoundException($this->id, $domainId);
    }
}
