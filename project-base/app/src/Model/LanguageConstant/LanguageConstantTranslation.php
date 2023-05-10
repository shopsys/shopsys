<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;

/**
 * @ORM\Table(name="language_constant_translations")
 * @ORM\Entity
 */
class LanguageConstantTranslation extends AbstractTranslation
{
    /**
     * @var \App\Model\LanguageConstant\LanguageConstant
     * @Prezent\Translatable(targetEntity="App\Model\LanguageConstant\LanguageConstant")
     */
    protected $translatable;

    /**
     * @ORM\Column(type="text")
     */
    private string $translation = '';

    /**
     * @return string|null
     */
    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     */
    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }
}
