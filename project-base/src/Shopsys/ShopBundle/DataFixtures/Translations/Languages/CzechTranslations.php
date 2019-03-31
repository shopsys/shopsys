<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\DataFixtures\Translations\Languages;

use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslationInterface;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

class CzechTranslations implements DataFixturesTranslationInterface
{
    /**
     * @var array
     */
    private $translations = [];

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return 'cs';
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        if (empty($this->translations)) {
            $this->initTranslations();
        }

        return $this->translations;
    }

    protected function initTranslations(): void
    {
    }
}
