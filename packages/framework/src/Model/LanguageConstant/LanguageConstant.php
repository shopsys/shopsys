<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="language_constants", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="language_constants_key", columns={"key"})
 * })
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantTranslation translation(?string $locale = null)
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
     * @var string
     * @ORM\Column(type="text")
     */
    protected $key;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantTranslation")
     */
    protected $translations;

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $languageConstantData
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getTranslation(?string $locale = null)
    {
        return $this->translation($locale)->getTranslation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData $constantData
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
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantTranslation
     */
    protected function createTranslation(): LanguageConstantTranslation
    {
        return new LanguageConstantTranslation();
    }
}
