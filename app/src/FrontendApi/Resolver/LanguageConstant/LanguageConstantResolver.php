<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\LanguageConstant;

use App\Model\LanguageConstant\LanguageConstantFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class LanguageConstantResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\LanguageConstant\LanguageConstantFacade
     */
    private LanguageConstantFacade $languageConstantFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\LanguageConstant\LanguageConstantFacade $languageConstantFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(LanguageConstantFacade $languageConstantFacade, Domain $domain)
    {
        $this->languageConstantFacade = $languageConstantFacade;
        $this->domain = $domain;
    }

    /**
     * @return array<int, array{key: string, translation: string}>
     */
    public function resolve(): array
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

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'languageConstants'];
    }
}
