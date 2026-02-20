<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Model\Country\Exception\CountryDomainNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @method \Shopsys\FrameworkBundle\Model\Country\CountryTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'countries')]
#[ORM\Entity]
class Country extends AbstractTranslatableEntity
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * Country code in ISO 3166-1 alpha-2
     *
     * @var string
     */
    #[ORM\Column(type: 'string', length: 2)]
    protected $code;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Country\CountryTranslation>
     */
    #[Prezent\Translations(targetEntity: CountryTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Country\CountryDomain>
     */
    #[ORM\OneToMany(targetEntity: CountryDomain::class, mappedBy: 'country', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected $domains;

    public function __construct(CountryData $countryData)
    {
        $this->translations = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->createDomains($countryData);
        $this->setData($countryData);
    }

    public function edit(CountryData $countryData): void
    {
        $this->setDomains($countryData);
        $this->setData($countryData);
    }

    protected function setData(CountryData $countryData): void
    {
        $this->code = $countryData->code;
        $this->setTranslations($countryData);
    }

    protected function setTranslations(CountryData $countryData): void
    {
        foreach ($countryData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    #[EntityLogIdentify(EntityLogIdentify::IS_LOCALIZED)]
    /**
     * @param string|null $locale
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    public function isEnabled(int $domainId): bool
    {
        return $this->getCountryDomain($domainId)->isEnabled();
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
    public function getCode()
    {
        return $this->code;
    }

    public function getPriority(int $domainId): int
    {
        return $this->getCountryDomain($domainId)->getPriority();
    }

    #[Override]
    protected function createTranslation(): CountryTranslation
    {
        return new CountryTranslation();
    }

    protected function setDomains(CountryData $countryData): void
    {
        foreach ($this->domains as $countryDomain) {
            $domainId = $countryDomain->getDomainId();
            $countryDomain->setEnabled($countryData->enabled[$domainId]);
            $countryDomain->setPriority($countryData->priority[$domainId] ?? 0);
        }
    }

    protected function createDomains(CountryData $countryData): void
    {
        $domainIds = array_keys($countryData->enabled);

        foreach ($domainIds as $domainId) {
            $countryDomain = new CountryDomain($this, $domainId);
            $this->domains->add($countryDomain);
        }

        $this->setDomains($countryData);
    }

    protected function getCountryDomain(int $domainId): CountryDomain
    {
        foreach ($this->domains as $countryDomain) {
            if ($countryDomain->getDomainId() === $domainId) {
                return $countryDomain;
            }
        }

        throw new CountryDomainNotFoundException($domainId, $this->id);
    }
}
