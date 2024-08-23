<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Prezent\Doctrine\Translatable\Entity\TranslatableTrait;
use Shopsys\FrameworkBundle\Model\Localization\Exception\ImplicitLocaleNotSetException;

trait TranslatableEntityTrait
{
    use TranslatableTrait;

    /**
     * @Prezent\CurrentLocale
     */
    protected ?string $currentLocale = null;

    protected ?AbstractTranslation $currentTranslation = null;

    /**
     * @return string|null
     */
    protected function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    /**
     * @param string $locale
     * @return \Prezent\Doctrine\Translatable\Entity\AbstractTranslation|null
     */
    protected function findTranslation($locale)
    {
        /** @var \Prezent\Doctrine\Translatable\Entity\AbstractTranslation[] $translations */
        $translations = $this->translations;

        foreach ($translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @param string|null $locale
     * @return \Prezent\Doctrine\Translatable\Entity\AbstractTranslation
     */
    protected function translation($locale = null)
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }

        if (!$locale) {
            throw new ImplicitLocaleNotSetException(
                $this,
                $this->id,
            );
        }

        if ($this->currentTranslation !== null && $this->currentTranslation->getLocale() === $locale) {
            return $this->currentTranslation;
        }

        $translation = $this->findTranslation($locale);

        if ($translation === null) {
            $translation = $this->createTranslation();
            $translation->setLocale($locale);
            $this->translations[$translation->getLocale()] = $translation;
            $translation->setTranslatable($this);
        }

        $this->currentTranslation = $translation;

        return $translation;
    }

    /**
     * @return \Prezent\Doctrine\Translatable\Entity\AbstractTranslation
     */
    abstract protected function createTranslation();
}