<?php

namespace Shopsys\FrameworkBundle\Model\Country;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="countries")
 * @ORM\Entity
 */
class Country extends AbstractTranslatableEntity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Country\CountryTranslation")
     */
    protected $translations;

    /**
     * Country code in ISO 3166-1 alpha-2
     * @var string|null
     *
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $code;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     * @param int $domainId
     */
    public function __construct(CountryData $countryData, $domainId)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($countryData);
        $this->domainId = $domainId;
        $this->code = $countryData->code;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     */
    public function edit(CountryData $countryData)
    {
        $this->setTranslations($countryData);
        $this->code = $countryData->code;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return null|string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return \Prezent\Doctrine\Translatable\TranslationInterface
     */
    protected function createTranslation()
    {
        return new \Shopsys\FrameworkBundle\Model\Transport\CountryTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $names
     */
    private function setTranslations(CountryData $countryData)
    {
        foreach ($countryData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName(string $locale = null)
    {
        return $this->translation($locale)->getName();
    }
}
