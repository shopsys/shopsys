<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

use Shopsys\FrameworkBundle\Maker\BaseMaker;
use Shopsys\FrameworkBundle\Maker\EntityMaker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EntityConfigFactory
{
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig
     */
    public function create(InputInterface $input, SymfonyStyle $io): EntityConfig
    {
        $entityConfig = $this->createWithEntityNameOnly($input);
        $entityConfig->tableName = $this->findOptionValue($input, EntityMaker::TABLE_NAME_OPTION) ?? $io->ask('What is the table name?', $this->convertEntityNameToTableName($entityConfig->entityName));
        $entityConfig->isTranslatable = $this->findOptionValue($input, EntityMaker::IS_TRANSLATABLE_OPTION) ?? $io->confirm('Is the entity translatable?', false);
        $entityConfig->hasId = $this->findOptionValue($input, EntityMaker::HAS_ID_OPTION) ?? $io->confirm('Does the entity have an ID?');
        $entityConfig->hasUuid = $this->findOptionValue($input, EntityMaker::HAS_UUID_OPTION) ?? $io->confirm('Does the entity have a UUID?');

        return $entityConfig;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig
     */
    public function createWithEntityNameOnly(InputInterface $input): EntityConfig
    {
        $entityConfig = new EntityConfig();

        $entityConfig->entityName = ucfirst($input->getArgument(BaseMaker::ENTITY_NAME_ARGUMENT));

        return $entityConfig;
    }

    /**
     * @param string $entityName
     * @return string
     */
    protected function convertEntityNameToTableName(string $entityName): string
    {
        $pattern = '/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=\d)/';
        $entityNameInSnakeCase = strtolower(preg_replace($pattern, '_', $entityName));

        $lastLetter = strtolower($entityNameInSnakeCase[strlen($entityNameInSnakeCase) - 1]);

        return match ($lastLetter) {
            'y' => substr($entityNameInSnakeCase, 0, -1) . 'ies',
            's' => $entityNameInSnakeCase . 'es',
            default => $entityNameInSnakeCase . 's',
        };
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param string $optionName
     * @return mixed
     */
    protected function findOptionValue(InputInterface $input, string $optionName): mixed
    {
        if ($input->hasOption($optionName)) {
            $value = $input->getOption($optionName);

            return $value !== null && $value !== '' ? $value : null;
        }

        return null;
    }
}
