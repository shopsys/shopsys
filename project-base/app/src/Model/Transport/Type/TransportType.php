<?php

declare(strict_types=1);

namespace App\Model\Transport\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="transport_types")
 * @ORM\Entity
 * @method \App\Model\Transport\Type\TransportTypeTranslation translation(?string $locale = null)
 */
class TransportType extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Transport\Type\TransportTypeTranslation>
     * @Prezent\Translations(targetEntity="App\Model\Transport\Type\TransportTypeTranslation")
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private string $code;

    /**
     * @param \App\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    public function __construct(TransportTypeData $transportTypeData)
    {
        $this->translations = new ArrayCollection();
        $this->setData($transportTypeData);
    }

    /**
     * @param \App\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    public function edit(TransportTypeData $transportTypeData): void
    {
        $this->setData($transportTypeData);
    }

    /**
     * @param \App\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    protected function setData(TransportTypeData $transportTypeData): void
    {
        $this->code = $transportTypeData->code;
        $this->setTranslations($transportTypeData);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null): ?string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param \App\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    protected function setTranslations(TransportTypeData $transportTypeData): void
    {
        foreach ($transportTypeData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \App\Model\Transport\Type\TransportTypeTranslation
     */
    protected function createTranslation(): TransportTypeTranslation
    {
        return new TransportTypeTranslation();
    }
}
