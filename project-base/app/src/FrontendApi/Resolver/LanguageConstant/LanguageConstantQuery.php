<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\LanguageConstant;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class LanguageConstantQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantFacade $languageConstantFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly LanguageConstantFacade $languageConstantFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @return array<int, array{key: string, translation: string}>
     */
    public function languageConstantsQuery(): array
    {
        $translations = [];
        $userTranslations = $this->languageConstantFacade->getUserTranslationsByLocaleIndexedByKey($this->domain->getLocale());

        foreach ($userTranslations as $key => $userTranslation) {
            $translations[] = [
                'key' => $key,
                'translation' => $userTranslation,
            ];
        }

        return $translations;
    }
}
