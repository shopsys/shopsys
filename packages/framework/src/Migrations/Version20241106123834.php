<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\Model\Localization\Exception\AdminLocaleNotFoundException;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241106123834 extends AbstractMigration implements DomainAwareInterface, AdministratorLocalizationAwareInterface
{
    use MultidomainMigrationTrait;

    protected ?AdministratorLocalizationFacade $administratorLocalizationFacade = null;

    #[Override]
    public function up(Schema $schema): void
    {
        $allowedAdminLocales = $this->administratorLocalizationFacade->getAllowedAdminLocales();
        $defaultLocale = reset($allowedAdminLocales);

        if ($defaultLocale === false) {
            throw new AdminLocaleNotFoundException();
        }

        $this->sql(sprintf('ALTER TABLE administrators ADD selected_locale VARCHAR(10) NOT NULL DEFAULT \'%s\'', $defaultLocale));
        $this->sql('ALTER TABLE administrators ALTER selected_locale DROP DEFAULT');
    }

    #[Override]
    public function setAdministratorLocalizationFacade(
        AdministratorLocalizationFacade $administratorLocalizationFacade,
    ): void {
        $this->administratorLocalizationFacade = $administratorLocalizationFacade;
    }
}
