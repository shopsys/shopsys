<?php

declare(strict_types=1);


namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

trait CachedTranslatableTrait
{
    /**
     * Get the translations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslations()
    {
        /** @var mixed $translations */
        $translations = $this->translations;
        if (is_array($translations)) {
            $this->translations = new ArrayCollection($this->translations);
        }

        /** @var ArrayCollection $translations */
        $translations = $this->translations;

        return $translations;
    }
}
