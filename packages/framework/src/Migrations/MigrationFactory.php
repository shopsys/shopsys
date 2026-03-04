<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory as DoctrineMigrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;

class MigrationFactory implements DoctrineMigrationFactory
{
    public function __construct(
        protected readonly DoctrineMigrationFactory $migrationFactory,
        protected readonly Domain $domain,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
        protected readonly CdnFacade $cdnFacade,
    ) {
    }

    #[Override]
    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $migration = $this->migrationFactory->createVersion($migrationClassName);

        if ($migration instanceof DomainAwareInterface) {
            $migration->setDomain($this->domain);
        }

        if ($migration instanceof EntityManagerAwareInterface) {
            $migration->setEntityManager($this->entityManager);
        }

        if ($migration instanceof AdministratorLocalizationAwareInterface) {
            $migration->setAdministratorLocalizationFacade($this->administratorLocalizationFacade);
        }

        if ($migration instanceof CdnAwareInterface) {
            $migration->setCdnFacade($this->cdnFacade);
        }

        return $migration;
    }
}
