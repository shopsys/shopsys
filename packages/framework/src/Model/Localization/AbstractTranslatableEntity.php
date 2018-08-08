<?php

namespace Shopsys\FrameworkBundle\Model\Localization;

use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslatable;

abstract class AbstractTranslatableEntity extends AbstractTranslatable
{
    /**
     * @Prezent\CurrentLocale
     */
    protected $currentLocale;

    /**
     * @var \Prezent\Doctrine\Translatable\TranslationInterface
     */
    protected $currentTranslation;

    protected function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }

    protected function findTranslation(string $locale): ?\Prezent\Doctrine\Translatable\TranslationInterface
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    protected function translation(?string $locale = null): \Prezent\Doctrine\Translatable\TranslationInterface
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }

        if (!$locale) {
            throw new \Shopsys\FrameworkBundle\Model\Localization\Exception\ImplicitLocaleNotSetException(
                $this,
                $this->id
            );
        }

        if ($this->currentTranslation && $this->currentTranslation->getLocale() === $locale) {
            return $this->currentTranslation;
        }

        $translation = $this->findTranslation($locale);
        if ($translation === null) {
            $translation = $this->createTranslation();
            $translation->setLocale($locale);
            $this->addTranslation($translation);
        }

        $this->currentTranslation = $translation;
        return $translation;
    }

    /**
     * @return \Prezent\Doctrine\Translatable\TranslationInterface
     */
    abstract protected function createTranslation(): \Prezent\Doctrine\Translatable\TranslationInterface;
}
