<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\DataFixtures\Translations;

interface DataFixturesTranslationInterface
{
    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @return array
     */
    public function getTranslations(): array;
}
