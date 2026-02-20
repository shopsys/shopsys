<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Localization\Exception\AdminLocaleNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class AdministratorLocalizationFacade
{
    /**
     * @param string[] $allowedAdminLocales
     */
    public function __construct(
        protected readonly Localization $localization,
        protected readonly array $allowedAdminLocales,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function setSelectedLocale(Administrator $administrator, string $locale): void
    {
        if (!$this->isAdminLocaleSupported($locale)) {
            throw new AdminLocaleNotFoundException($locale, $this->allowedAdminLocales);
        }

        $administrator->setSelectedLocale($locale);

        $this->em->flush();
    }

    public function getCurrentAdminLocaleOrDefault(): string
    {
        try {
            $administrator = $this->administratorFrontSecurityFacade->getCurrentAdministrator();
            $adminLocale = $administrator->getSelectedLocale();

            if (!$this->isAdminLocaleSupported($adminLocale)) {
                $adminLocale = $this->getDefaultAdminLocale();
                $this->setSelectedLocale($administrator, $adminLocale);
            }
        } catch (AdministratorIsNotLoggedException) {
            $adminLocale = $this->getDefaultAdminLocale();
        }


        return $adminLocale;
    }

    /**
     * @return string[]
     */
    public function getAllowedAdminLocales(): array
    {
        return $this->allowedAdminLocales;
    }

    public function getDefaultAdminLocale(): string
    {
        $allowedAdminLocales = $this->allowedAdminLocales;
        $defaultAdminLocale = array_first($allowedAdminLocales);

        if ($defaultAdminLocale === null) {
            throw new AdminLocaleNotFoundException();
        }

        return $defaultAdminLocale;
    }

    protected function isAdminLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->allowedAdminLocales, true);
    }
}
