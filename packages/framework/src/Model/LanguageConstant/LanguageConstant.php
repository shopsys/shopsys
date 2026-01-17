<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @method \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'language_constants')]
#[ORM\UniqueConstraint(name: 'language_constants_key_namespace', columns: ['key', 'namespace'])]
#[ORM\Entity]
class LanguageConstant extends AbstractTranslatableEntity
{
    public const string NAMESPACE_COMMON = 'common';

    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    protected $key;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    protected $namespace;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantTranslation>
     */
    #[Prezent\Translations(targetEntity: LanguageConstantTranslation::class)]
    protected $translations;

    public function __construct(LanguageConstantData $languageConstantData)
    {
        $this->key = $languageConstantData->key;
        $this->namespace = $languageConstantData->namespace;
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
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getTranslation(?string $locale = null)
    {
        return $this->translation($locale)->getTranslation();
    }

    public function editTranslation(LanguageConstantData $constantData): void
    {
        $this->translation($constantData->locale)->setTranslation($constantData->userTranslation);
    }

    public function deleteTranslation(string $locale): void
    {
        $this->removeTranslation($this->translation($locale));
    }

    #[Override]
    protected function createTranslation(): LanguageConstantTranslation
    {
        return new LanguageConstantTranslation();
    }
}
