<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

class LanguageConstantData
{
    /**
     * @var string
     */
    public string $key;

    /**
     * @var string
     */
    public string $locale;

    /**
     * @var string
     */
    public string $originalTranslation;

    /**
     * @var string
     */
    public string $userTranslation;
}
