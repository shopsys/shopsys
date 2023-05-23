<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

class DbIndexesFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Localization\DbIndexesRepository $dbIndexesRepository
     */
    public function __construct(
        protected readonly Localization $localization,
        protected readonly DbIndexesRepository $dbIndexesRepository,
    ) {
    }

    public function updateLocaleSpecificIndexes(): void
    {
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $domainCollation = $this->localization->getCollationByLocale($locale);
            $this->dbIndexesRepository->updateProductTranslationNameIndexForLocaleAndCollation(
                $locale,
                $domainCollation,
            );
        }
    }
}
