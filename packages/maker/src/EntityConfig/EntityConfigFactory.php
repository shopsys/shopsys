<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

use Shopsys\MakerBundle\Maker\BaseMaker;
use Shopsys\MakerBundle\Utils\NamingHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntityConfigFactory
{
    public function create(InputInterface $input, SymfonyStyle $io): EntityConfig
    {
        $entityConfig = $this->createWithEntityNameOnly($input);
        $entityConfig->tableName = $io->ask('What is the table name?', NamingHelper::convertEntityNameToTableName($entityConfig->entityName));
        $entityConfig->isTranslatable = $io->confirm('Is the entity translatable?', false);
        $entityConfig->isMultiDomain = $io->confirm('Is the entity multi domain?', false);
        $entityConfig->hasId = $io->confirm('Does the entity have an ID?');
        $entityConfig->hasUuid = $io->confirm('Does the entity have a UUID?');

        return $entityConfig;
    }

    public function createWithEntityNameOnly(InputInterface $input): EntityConfig
    {
        $entityConfig = new EntityConfig();

        $entityConfig->entityName = ucfirst($input->getArgument(BaseMaker::ENTITY_NAME_ARGUMENT));

        return $entityConfig;
    }
}
