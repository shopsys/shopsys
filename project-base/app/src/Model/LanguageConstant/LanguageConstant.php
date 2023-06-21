<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="language_constants", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="language_constants_key", columns={"key"})
 * })
 * @ORM\Entity
 * @method \App\Model\LanguageConstant\LanguageConstantTranslation translation(?string $locale = null)
 */
class LanguageConstant extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    private string $key;

    /**
     * @var \App\Model\LanguageConstant\LanguageConstantTranslation[]|\Doctrine\Common\Collections\Collection
     * @Prezent\Translations(targetEntity="App\Model\LanguageConstant\LanguageConstantTranslation")
     */
    protected $translations;

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantData $languageConstantData
     */
    public function __construct(LanguageConstantData $languageConstantData)
    {
        $this->key = $languageConstantData->key;
        $this->translations = new ArrayCollection();
        $this->translation($languageConstantData->locale)->setTranslation($languageConstantData->userTranslation);
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
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getTranslation(?string $locale = null): string
    {
        return $this->translation($locale)->getTranslation();
    }

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantData $constantData
     */
    public function editTranslation(LanguageConstantData $constantData): void
    {
        $this->translation($constantData->locale)->setTranslation($constantData->userTranslation);
    }

    /**
     * @param string $locale
     */
    public function deleteTranslation(string $locale): void
    {
        $this->removeTranslation($this->translation($locale));
    }

    /**
     * @return \App\Model\LanguageConstant\LanguageConstantTranslation
     */
    protected function createTranslation(): LanguageConstantTranslation
    {
        return new LanguageConstantTranslation();
    }
}
