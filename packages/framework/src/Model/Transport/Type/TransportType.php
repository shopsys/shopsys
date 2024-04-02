<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="transport_types")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeTranslation translation(?string $locale = null)
 * @phpstan-ignore-next-line the entity creation is not supported so the required factory (TransportTypeFactory) would be unused
 */
class TransportType extends AbstractTranslatableEntity
{
    public const string TYPE_COMMON = 'common';
    public const string TYPE_PACKETERY = 'packetery';
    public const string TYPE_PERSONAL_PICKUP = 'personal_pickup';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeTranslation")
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=25, unique=true)
     */
    protected $code;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    public function __construct(TransportTypeData $transportTypeData)
    {
        $this->translations = new ArrayCollection();
        $this->setData($transportTypeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    public function edit(TransportTypeData $transportTypeData): void
    {
        $this->setData($transportTypeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    protected function setData(TransportTypeData $transportTypeData): void
    {
        $this->code = $transportTypeData->code;
        $this->setTranslations($transportTypeData);
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

    /**
     * @param string|null $locale
     * @return string|null
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeData $transportTypeData
     */
    protected function setTranslations(TransportTypeData $transportTypeData): void
    {
        foreach ($transportTypeData->names as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeTranslation
     */
    protected function createTranslation(): TransportTypeTranslation
    {
        return new TransportTypeTranslation();
    }
}
