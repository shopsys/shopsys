<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\AdditionalService\Exception\AdditionalServiceDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'additional_services')]
#[ORM\Entity]
#[EntityImage]
class AdditionalService extends AbstractTranslatableEntity implements OrderableEntityInterface, Presentable
{
    protected const int GEDMO_SORTABLE_LAST_POSITION = -1;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    protected $catnum;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceTranslation>
     */
    #[Prezent\Translations(targetEntity: AdditionalServiceTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDomain>
     */
    #[ORM\OneToMany(targetEntity: AdditionalServiceDomain::class, mappedBy: 'additionalService', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $zboziServiceType;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $deliveryDaysExtension;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[Gedmo\SortablePosition]
    protected $position;

    public function __construct(AdditionalServiceData $additionalServiceData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->createDomains($additionalServiceData);
        $this->position = static::GEDMO_SORTABLE_LAST_POSITION;
        $this->uuid = $additionalServiceData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($additionalServiceData);
    }

    public function edit(AdditionalServiceData $additionalServiceData): void
    {
        $this->setDomains($additionalServiceData);
        $this->setData($additionalServiceData);
    }

    protected function setData(AdditionalServiceData $additionalServiceData): void
    {
        $this->catnum = $additionalServiceData->catnum;
        $this->zboziServiceType = $additionalServiceData->zboziServiceType;
        $this->deliveryDaysExtension = $additionalServiceData->deliveryDaysExtension;
        $this->setTranslations($additionalServiceData);
    }

    protected function setTranslations(AdditionalServiceData $additionalServiceData): void
    {
        foreach ($additionalServiceData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }

        foreach ($additionalServiceData->feedName as $locale => $feedName) {
            $this->translation($locale)->setFeedName($feedName);
        }

        foreach ($additionalServiceData->zboziDescription as $locale => $zboziDescription) {
            $this->translation($locale)->setZboziDescription($zboziDescription);
        }

        foreach ($additionalServiceData->description as $locale => $description) {
            $this->translation($locale)->setDescription($description);
        }
    }

    protected function createDomains(AdditionalServiceData $additionalServiceData): void
    {
        $domainIds = array_keys($additionalServiceData->enabledByDomainId);

        foreach ($domainIds as $domainId) {
            $additionalServiceDomain = new AdditionalServiceDomain($this, $domainId);
            $this->domains->add($additionalServiceDomain);
        }

        $this->setDomains($additionalServiceData);
    }

    protected function setDomains(AdditionalServiceData $additionalServiceData): void
    {
        foreach ($this->domains as $additionalServiceDomain) {
            $domainId = $additionalServiceDomain->getDomainId();
            $additionalServiceDomain->setEnabled($additionalServiceData->enabledByDomainId[$domainId]);
            $additionalServiceDomain->setShowInFeeds($additionalServiceData->showInFeedsByDomainId[$domainId]);
            $additionalServiceDomain->setUseProductVatRate($additionalServiceData->useProductVatRateByDomainId[$domainId]);
            $additionalServiceDomain->setVat(
                $additionalServiceData->useProductVatRateByDomainId[$domainId] ? null : $additionalServiceData->vatsIndexedByDomainId[$domainId],
            );
            $additionalServiceDomain->setPrice($additionalServiceData->pricesIndexedByDomainId[$domainId]);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new AdditionalServiceTranslation();
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
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getCatnum()
    {
        return $this->catnum;
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
    public function getFeedName($locale = null)
    {
        return $this->translation($locale)->getFeedName();
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getZboziDescription($locale = null)
    {
        return $this->translation($locale)->getZboziDescription();
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
     * @return string|null
     */
    public function getZboziServiceType()
    {
        return $this->zboziServiceType;
    }

    /**
     * @return int
     */
    public function getDeliveryDaysExtension()
    {
        return $this->deliveryDaysExtension;
    }

    /**
     * @return int
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
    public function isEnabled(int $domainId)
    {
        return $this->getAdditionalServiceDomain($domainId)->isEnabled();
    }

    /**
     * @return bool
     */
    public function isShownInFeeds(int $domainId)
    {
        return $this->getAdditionalServiceDomain($domainId)->isShownInFeeds();
    }

    /**
     * @return bool
     */
    public function isProductVatRateUsed(int $domainId)
    {
        return $this->getAdditionalServiceDomain($domainId)->isProductVatRateUsed();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function getVatForDomain(int $domainId)
    {
        return $this->getAdditionalServiceDomain($domainId)->getVat();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceForDomain(int $domainId)
    {
        return $this->getAdditionalServiceDomain($domainId)->getPrice();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDomain
     */
    public function getAdditionalServiceDomain(int $domainId)
    {
        foreach ($this->domains as $additionalServiceDomain) {
            if ($additionalServiceDomain->getDomainId() === $domainId) {
                return $additionalServiceDomain;
            }
        }

        throw new AdditionalServiceDomainNotFoundException($domainId, $this->id);
    }

    #[Override]
    public function toHumanReadable(): string
    {
        return $this->getName() ?? (string)$this->getId();
    }
}
