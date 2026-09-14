<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Product\Flag\Exception\FlagDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'flags')]
#[ORM\Entity]
class Flag extends AbstractTranslatableEntity
{
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
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation>
     */
    #[Prezent\Translations(targetEntity: FlagTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 7)]
    protected $rgbColor;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $visible;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $lockedForDeletion;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'promotion_xy_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ProductPromotionXy::class)]
    protected $promotionXy;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDomain>
     */
    #[ORM\OneToMany(targetEntity: FlagDomain::class, mappedBy: 'flag', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    public function __construct(FlagData $flagData)
    {
        $this->uuid = $flagData->uuid ?: Uuid::uuid4()->toString();

        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->createDomains($flagData);
        $this->setData($flagData);
        $this->lockedForDeletion = false;
    }

    public function edit(FlagData $flagData): void
    {
        $this->setDomains($flagData);
        $this->setData($flagData);
    }

    protected function setData(FlagData $flagData): void
    {
        $this->setTranslations($flagData);
        $this->rgbColor = $flagData->rgbColor;
        $this->visible = $flagData->visible;
        $this->promotionXy = $flagData->promotionXy;
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
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string[]
     */
    public function getNames()
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @return string
     */
    public function getRgbColor()
    {
        return $this->rgbColor;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return bool
     */
    public function hasPromotionXy()
    {
        return $this->promotionXy !== null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
    public function getPromotionXy()
    {
        return $this->promotionXy;
    }

    protected function setDomains(FlagData $flagData): void
    {
        foreach ($this->domains as $flagDomain) {
            $flagDomain->getSeoAttributes()->edit($flagData->seo[$flagDomain->getDomainId()]);
        }
    }

    protected function createDomains(FlagData $flagData): void
    {
        $domainIds = array_keys($flagData->seo);

        foreach ($domainIds as $domainId) {
            $flagDomain = new FlagDomain($this, $domainId);
            $this->domains->add($flagDomain);
        }

        $this->setDomains($flagData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDomain
     */
    protected function getFlagDomain(int $domainId)
    {
        foreach ($this->domains as $domain) {
            if ($domain->getDomainId() === $domainId) {
                return $domain;
            }
        }

        throw new FlagDomainNotFoundException($domainId, $this->id);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\SeoAttributes
     */
    public function getSeoAttributes(int $domainId)
    {
        return $this->getFlagDomain($domainId)->getSeoAttributes();
    }

    protected function setTranslations(FlagData $flagData): void
    {
        foreach ($flagData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new FlagTranslation();
    }

    /**
     * @return bool
     */
    public function isLockedForDeletion()
    {
        return $this->lockedForDeletion;
    }
}
