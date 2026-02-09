<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Shopsys\MakerBundle\Utils\EntityChoiceHelper;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

/**
 * Heavily inspired (and copied) from @see \Symfony\Bundle\MakerBundle\Maker\MakeEntity
 */
class EntityFieldsConfigurator
{
    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly EntityChoiceHelper $entityChoicesHelper,
    ) {
    }

    public function configureEntityFields(EntityConfig $entityConfig, ConsoleStyle $io): void
    {
        $io->writeln($this->getConfigurePropertiesMessage($entityConfig));
        $this->askForFields($io, $entityConfig, EntityTypeEnum::ENTITY);

        if ($entityConfig->isTranslatable) {
            $io->writeln(sprintf('<info>Now let\'s configure the properties of <comment>%s</comment> entity.</info>', $entityConfig->entityName . 'Translation'));
            $this->askForFields($io, $entityConfig, EntityTypeEnum::TRANSLATION);
        }

        if (!$entityConfig->isMultiDomain) {
            return;
        }

        $io->writeln(sprintf('<info>Now let\'s configure the properties of <comment>%s</comment> entity.</info>', $entityConfig->entityName . 'Domain'));
        $this->askForFields($io, $entityConfig, EntityTypeEnum::DOMAIN);
    }

    /**
     * @param string[] $fields
     */
    protected function askForNextField(
        ConsoleStyle $io,
        array $fields,
        bool $isFirstField,
        EntityConfig $entityConfig,
        EntityTypeEnum $entityType,
    ): ?Property {
        $io->writeln('');

        if ($isFirstField) {
            $questionText = 'New property name (press <return> to stop adding fields)';
        } else {
            $questionText = 'Add another property? Enter the property name (or press <return> to stop adding fields)';
        }

        $fieldName = $io->ask($questionText, null, function ($name) use ($fields) {
            // allow it to be empty
            if (!$name) {
                return $name;
            }

            if (in_array($name, $fields, true)) {
                throw new InvalidArgumentException(sprintf('The "%s" property already exists.', $name));
            }

            return Validator::validateDoctrineFieldName($name, $this->managerRegistry);
        });

        if (!$fieldName) {
            return null;
        }

        $defaultType = 'string';
        // try to guess the type by the field name prefix/suffix
        // convert to snake case for simplicity
        $snakeCasedField = Str::asSnakeCase($fieldName);
        $suffix = substr($snakeCasedField, -3);

        if ($suffix === '_at') {
            $defaultType = 'datetime_immutable';
        } elseif ($suffix === '_id') {
            $defaultType = 'integer';
        } elseif (str_starts_with($snakeCasedField, 'is_')) {
            $defaultType = 'boolean';
        } elseif (str_starts_with($snakeCasedField, 'has_')) {
            $defaultType = 'boolean';
        } elseif ($snakeCasedField === 'uuid') {
            $defaultType = Type::hasType('uuid') ? 'uuid' : 'guid';
        } elseif ($snakeCasedField === 'guid') {
            $defaultType = 'guid';
        }

        $type = null;
        $types = $this->getTypesMap();

        $allValidTypes = array_merge(
            array_keys($types),
            EntityRelationTypeEnum::getAllValues(),
            ['relation'],
        );

        while ($type === null) {
            $question = new Question('Field type (enter <comment>?</comment> to see all types)', $defaultType);
            $question->setAutocompleterValues($allValidTypes);
            $type = $io->askQuestion($question);

            if ($type === '?') {
                $this->printAvailableTypes($io);
                $io->writeln('');

                $type = null;
            } elseif (!in_array($type, $allValidTypes, true)) {
                $this->printAvailableTypes($io);
                $io->error(sprintf('Invalid type "%s".', $type));
                $io->writeln('');

                $type = null;
            }
        }

        if ($type === 'relation' || in_array($type, EntityRelationTypeEnum::getAllValues(), true)) {
            return $this->askRelationDetails($io, $entityConfig->getEntityFullyQualifiedName($entityType), $type, $fieldName, $entityType);
        }

        $entityProperty = new ScalarProperty($fieldName, $type, $entityType);

        if ($type === 'string') {
            // default to 255, avoid the question
            $entityProperty->length = $io->ask('Field length', '255', Validator::validateLength(...));
        } elseif ($type === 'decimal' || $type === 'money') {
            $entityProperty->precision = $io->ask('Precision (total number of digits stored: 100.00 would be 5)', '20', Validator::validatePrecision(...));
            $entityProperty->scale = $io->ask('Scale (number of decimals to store: 100.00 would be 2)', '6', Validator::validateScale(...));
        } elseif ($type === 'enum') {
            // ask for valid backed enum class
            $entityProperty->enumType = $io->ask('Enum class', null, Validator::classIsBackedEnum(...));

            // set type according to user decision
            $entityProperty->type = $io->confirm('Can this field store multiple enum values', false) ? 'simple_array' : 'string';
        }

        if ($io->confirm('Can this field be null in the database (nullable)', false)) {
            $entityProperty->nullable = true;
        }

        return $entityProperty;
    }

    protected function printAvailableTypes(ConsoleStyle $io): void
    {
        $allTypes = $this->getTypesMap();

        $typesTable = [
            'main' => [
                'string' => ['ascii_string'],
                'text' => [],
                'boolean' => [],
                'integer' => ['smallint', 'bigint'],
                'float' => [],
            ],
            'array_object' => [
                'array' => ['simple_array'],
                'json' => [],
                'object' => [],
                'binary' => [],
                'blob' => [],
            ],
            'date_time' => [
                'datetime' => ['datetime_immutable'],
                'datetimetz' => ['datetimetz_immutable'],
                'date' => ['date_immutable'],
                'time' => ['time_immutable'],
                'dateinterval' => [],
            ],
            'other' => [
                'enum' => [],
            ],
        ];

        $printSection = static function (array $sectionTypes) use ($io, &$allTypes): void {
            foreach ($sectionTypes as $mainType => $subTypes) {
                if (!array_key_exists($mainType, $allTypes)) {
                    // The type is not a valid DBAL Type - don't show it as an option
                    continue;
                }

                foreach ($subTypes as $key => $potentialType) {
                    if (!array_key_exists($potentialType, $allTypes)) {
                        // The type is not a valid DBAL Type - don't show it as an "or" option
                        unset($subTypes[$key]);
                    }

                    // Remove type as not to show it again in "Other Types"
                    unset($allTypes[$potentialType]);
                }

                // Remove type as not to show it again in "Other Types"
                unset($allTypes[$mainType]);

                $line = sprintf('  * <comment>%s</comment>', $mainType);

                if (count($subTypes) > 0) {
                    $line .= sprintf(
                        ' or %s',
                        implode(' or ', array_map(
                            static fn ($subType) => sprintf('<comment>%s</comment>', $subType),
                            $subTypes,
                        )),
                    );
                }

                $io->writeln($line);
            }

            $io->writeln('');
        };

        $printRelationsSection = static function () use ($io): void {
            if (getenv('TERM_PROGRAM') === 'Hyper') {
                $wizard = 'wizard 🧙';
            } else {
                $wizard = DIRECTORY_SEPARATOR === '\\' ? 'wizard' : 'wizard 🧙';
            }

            $io->writeln(sprintf('  * <comment>relation</comment> a %s will help you build the relation', $wizard));

            foreach (EntityRelationTypeEnum::getAllValues() as $relation) {
                $line = sprintf('  * <comment>%s</comment>', $relation);

                $io->writeln($line);
            }

            $io->writeln('');
        };

        $io->writeln('<info>Main Types</info>');
        $printSection($typesTable['main']);

        $io->writeln('<info>Relationships/Associations</info>');
        $printRelationsSection();

        $io->writeln('<info>Array/Object Types</info>');
        $printSection($typesTable['array_object']);

        $io->writeln('<info>Date/Time Types</info>');
        $printSection($typesTable['date_time']);

        $io->writeln('<info>Other Types</info>');
        // empty the values
        $allTypes = array_map(static fn () => [], $allTypes);
        $allTypes = [...$typesTable['other'], ...$allTypes];
        $printSection($allTypes);
    }

    /**
     * @return array<string, string>
     */
    protected function getTypesMap(): array
    {
        return Type::getTypesMap();
    }

    /**
     * @return string[]
     */
    protected function getPropertyNames(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflectionClass = new ReflectionClass($class);

        return array_map(static fn (ReflectionProperty $prop) => $prop->getName(), $reflectionClass->getProperties());
    }

    protected function askForFields(
        ConsoleStyle $io,
        EntityConfig $entityConfig,
        EntityTypeEnum $entityType,
    ): void {
        $isFirstField = true;
        $currentFields = $this->getPropertyNames($entityConfig->getEntityFullyQualifiedName($entityType));

        while (true) {
            $newField = $this->askForNextField($io, $currentFields, $isFirstField, $entityConfig, $entityType);
            $isFirstField = false;

            if ($newField === null) {
                break;
            }

            $entityConfig->addProperty($newField);
            $currentFields[] = $newField->propertyName;
        }
    }

    protected function getConfigurePropertiesMessage(EntityConfig $entityConfig): string
    {
        $configurePropertiesMessage = sprintf('<info>Let\'s configure the properties of <comment>%s</comment> entity.</info>', $entityConfig->entityName);

        $additionalEntitiesNames = [];

        if ($entityConfig->isTranslatable) {
            $additionalEntitiesNames[] = sprintf(
                '<comment>%sTranslation</comment>',
                $entityConfig->entityName,
            );
        }

        if ($entityConfig->isMultiDomain) {
            $additionalEntitiesNames[] = sprintf(
                '<comment>%sDomain</comment>',
                $entityConfig->entityName,
            );
        }

        if (count($additionalEntitiesNames) > 0) {
            $entityLabel = count($additionalEntitiesNames) > 1 ? 'entities' : 'entity';

            $configurePropertiesMessage .= sprintf(
                ' <info>We will configure the properties of %s %s afterward.</info>',
                implode(' and ', $additionalEntitiesNames),
                $entityLabel,
            );
        }

        return $configurePropertiesMessage;
    }

    protected function askRelationDetails(
        ConsoleStyle $io,
        string $generatedEntityClass,
        string $type,
        string $newFieldName,
        EntityTypeEnum $entityType,
    ): RelationProperty {
        $targetEntityClass = $this->entityChoicesHelper->askForEntity($io, 'What class should this entity be related to?');

        if ($type === 'relation') {
            $type = $this->askRelationType($io, $generatedEntityClass, $targetEntityClass);
        }

        $askInversePropertyName = fn (RelationProperty $entityRelation) => $io->ask(
            sprintf(
                'What will be the property name in the <comment>%s</comment> class that should be used to map the relationship using "%s" setting? %s',
                Str::getShortClassName($entityRelation->relationTargetEntity),
                $entityRelation->getInverseSettingName($entityRelation->relationType),
                $entityRelation->relationType !== EntityRelationTypeEnum::ONE_TO_MANY ? 'If you do not want to define this setting, i.e. you want the relation to be unidirectional, just press <return>.' : '',
            ),
            $entityRelation->relationType === EntityRelationTypeEnum::ONE_TO_MANY ? Str::asLowerCamelCase(Str::getShortClassName($entityRelation->relationOwningClass)) : null,
        );

        $askIsNullable = static fn (string $propertyName, string $targetClass) => $io->confirm(sprintf(
            'Is the <comment>%s</comment>.<comment>%s</comment> property allowed to be null (nullable)?',
            Str::getShortClassName($targetClass),
            $propertyName,
        ));

        $askOrphanRemoval = static function (string $owningClass, string $inverseClass) use ($io) {
            $io->text([
                'Do you want to activate <comment>orphanRemoval</comment> on your relationship?',
                sprintf(
                    'A <comment>%s</comment> is "orphaned" when it is removed from its related <comment>%s</comment>.',
                    Str::getShortClassName($owningClass),
                    Str::getShortClassName($inverseClass),
                ),
                sprintf(
                    'e.g. <comment>$%s->remove%s($%s)</comment>',
                    Str::asLowerCamelCase(Str::getShortClassName($inverseClass)),
                    Str::asCamelCase(Str::getShortClassName($owningClass)),
                    Str::asLowerCamelCase(Str::getShortClassName($owningClass)),
                ),
                '',
                sprintf(
                    'NOTE: If a <comment>%s</comment> may *change* from one <comment>%s</comment> to another, answer "no".',
                    Str::getShortClassName($owningClass),
                    Str::getShortClassName($inverseClass),
                ),
            ]);

            return $io->confirm(sprintf('Do you want to automatically delete orphaned <comment>%s</comment> objects (orphanRemoval)?', $owningClass), false);
        };

        $relation = new RelationProperty(
            $newFieldName,
            EntityRelationTypeEnum::from($type),
            $generatedEntityClass,
            $targetEntityClass,
            $entityType,
        );

        $relation->inverseProperty = $askInversePropertyName($relation);

        if ($relation->relationType === EntityRelationTypeEnum::MANY_TO_ONE) {
            $relation->isNullable = $askIsNullable(
                $relation->propertyName,
                $relation->relationOwningClass,
            );
        }

        if ($relation->relationType === EntityRelationTypeEnum::ONE_TO_MANY) {
            $relation->orphanRemoval = $askOrphanRemoval(
                $relation->relationOwningClass,
                $relation->relationTargetEntity,
            );
        }

        return $relation;
    }

    protected function askRelationType(ConsoleStyle $io, string $entityClass, string $targetEntityClass): string
    {
        $io->writeln('What type of relationship is this?');

        $originalEntityShort = Str::getShortClassName($entityClass);
        $targetEntityShort = Str::getShortClassName($targetEntityClass);

        $rows = [];
        $rows[] = [
            EntityRelationTypeEnum::MANY_TO_ONE->value,
            sprintf("Each <comment>%s</comment> relates to (has) <info>one</info> <comment>%s</comment>.\nEach <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelationTypeEnum::ONE_TO_MANY->value,
            sprintf("Each <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.\nEach <comment>%s</comment> relates to (has) <info>one</info> <comment>%s</comment>.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelationTypeEnum::MANY_TO_MANY->value,
            sprintf("Each <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.\nEach <comment>%s</comment> can also relate to (can also have) <info>many</info> <comment>%s</comment> objects.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];

        $io->table([
            'Type',
            'Description',
        ], $rows);

        $question = new Question(sprintf(
            'Relation type? [%s]',
            implode(', ', EntityRelationTypeEnum::getAllValues()),
        ));
        $question->setAutocompleterValues(EntityRelationTypeEnum::getAllValues());
        $question->setValidator(function ($type) {
            if (!in_array($type, EntityRelationTypeEnum::getAllValues(), true)) {
                throw new InvalidArgumentException(sprintf('Invalid type: use one of: %s', implode(', ', EntityRelationTypeEnum::getAllValues())));
            }

            return $type;
        });

        return $io->askQuestion($question);
    }
}
