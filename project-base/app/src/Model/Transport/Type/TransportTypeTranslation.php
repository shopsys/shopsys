<?php

declare(strict_types=1);

namespace App\Model\Transport\Type;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Shopsys\FrameworkBundle\Component\String\TransformString;

/**
 * @ORM\Table(name="transport_type_translations")
 * @ORM\Entity
 */
class TransportTypeTranslation extends AbstractTranslation
{
    /**
     * @Prezent\Translatable(targetEntity="App\Model\Transport\Type\TransportType")
     */
    protected $translatable;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $name;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = TransformString::getTrimmedStringOrNullOnEmpty($name);
    }
}
