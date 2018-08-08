<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="availabilities")
 * @ORM\Entity
 */
class Availability extends AbstractTranslatableEntity
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
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation")
     */
    protected $translations;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $dispatchTime;

    public function __construct(AvailabilityData $availabilityData)
    {
        $this->translations = new ArrayCollection();
        $this->setTranslations($availabilityData);
        $this->dispatchTime = $availabilityData->dispatchTime;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    protected function setTranslations(AvailabilityData $availabilityData)
    {
        foreach ($availabilityData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityTranslation
    {
        return new AvailabilityTranslation();
    }

    public function edit(AvailabilityData $availabilityData)
    {
        $this->setTranslations($availabilityData);
        $this->dispatchTime = $availabilityData->dispatchTime;
    }

    public function getDispatchTime(): ?int
    {
        return $this->dispatchTime;
    }
}
