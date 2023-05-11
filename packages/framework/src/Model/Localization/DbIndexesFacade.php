<?php

namespace Shopsys\FrameworkBundle\Model\Localization;

class DbIndexesFacade
{
    protected Localization $localization;

    protected DbIndexesRepository $dbIndexesRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Localization\DbIndexesRepository $dbIndexesRepository
     */
    public function __construct(Localization $localization, DbIndexesRepository $dbIndexesRepository)
    {
        $this->localization = $localization;
        $this->dbIndexesRepository = $dbIndexesRepository;
    }

    public function updateLocaleSpecificIndexes(): void
    {
        foreach ($this->localization->getLocalesOfAllDomains() as $locale) {
            $domainCollation = $this->localization->getCollationByLocale($locale);
            $this->dbIndexesRepository->updateProductTranslationNameIndexForLocaleAndCollation(
                $locale,
                $domainCollation
            );
        }
    }
}
