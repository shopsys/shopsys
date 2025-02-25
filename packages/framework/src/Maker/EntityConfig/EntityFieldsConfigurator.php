<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;

/**
 * Heavily inspired (and copied) from @see \Symfony\Bundle\MakerBundle\Maker\MakeEntity
 */
class EntityFieldsConfigurator
{
    /**
     * @param \Doctrine\Persistence\ManagerRegistry $managerRegistry
     */
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entityConfig
     * @param \Symfony\Bundle\MakerBundle\ConsoleStyle $io
     */
    public function configureEntityFields(EntityConfig $entityConfig, ConsoleStyle $io): void
    {
        $configurePropertiesMessage = sprintf('<info>Let\'s configure the properties of <comment>%s</comment> entity.</info>', $entityConfig->entityName);

        if ($entityConfig->isTranslatable) {
            $configurePropertiesMessage .= sprintf(' <info>We will configure the properties of <comment>%s</comment> entity afterward.</info>', $entityConfig->entityName . 'Translation');
        }

        $io->writeln($configurePropertiesMessage);
        $this->askForFields($io, $entityConfig, PropertyTargetEnum::ENTITY);

        if ($entityConfig->isTranslatable) {
            $io->writeln(sprintf('<info>Now let\'s configure the properties of <comment>%s</comment> entity.</info>', $entityConfig->entityName . 'Translation'));
            $this->askForFields($io, $entityConfig, PropertyTargetEnum::TRANSLATION);
        }
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\ConsoleStyle $io
     * @param string[] $fields
     * @param bool $isFirstField
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityProperty|null
     */
    private function askForNextField(ConsoleStyle $io, array $fields, bool $isFirstField): ?EntityProperty
    {
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

        $allValidTypes = array_keys($types);

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

        $entityProperty = new EntityProperty($fieldName, $type);

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

    /**
     * @param \Symfony\Bundle\MakerBundle\ConsoleStyle $io
     */
    private function printAvailableTypes(ConsoleStyle $io): void
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

        $printSection = static function (array $sectionTypes) use ($io, &$allTypes) {
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

        $io->writeln('<info>Main Types</info>');
        $printSection($typesTable['main']);

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
    private function getTypesMap(): array
    {
        return Type::getTypesMap();
    }

    /**
     * @param string $class
     * @return string[]
     */
    private function getPropertyNames(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflectionClass = new ReflectionClass($class);

        return array_map(static fn (ReflectionProperty $prop) => $prop->getName(), $reflectionClass->getProperties());
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\ConsoleStyle $io
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig $entityConfig
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\PropertyTargetEnum $propertyTarget
     */
    private function askForFields(
        ConsoleStyle $io,
        EntityConfig $entityConfig,
        PropertyTargetEnum $propertyTarget,
    ): void {
        $isFirstField = true;
        $currentFields = $this->getPropertyNames($entityConfig->getEntityFullyQualifiedName());

        while (true) {
            $newField = $this->askForNextField($io, $currentFields, $isFirstField);
            $isFirstField = false;

            if ($newField === null) {
                break;
            }

            if (!($newField instanceof EntityProperty)) {
                throw new Exception('Invalid value');
            }

            $currentFields[] = $newField->propertyName;
            $newField->propertyTarget = $propertyTarget;
            $entityConfig->addProperty($newField);
        }
    }
}
