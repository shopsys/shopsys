<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

#[ORM\Table(name: 'language_constant_translations')]
#[ORM\Entity]
class LanguageConstantTranslation extends AbstractTranslation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant
     */
    #[Prezent\Translatable(targetEntity: LanguageConstant::class)]
    #[Override]
    protected $translatable;

    #[ORM\Column(type: 'text')]
    protected $translation;

    /**
     * @return string|null
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     */
    public function setTranslation($translation): void
    {
        $this->translation = $translation;
    }
}
