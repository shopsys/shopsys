<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Override;
use Shopsys\MakerBundle\Utils\EntityChoiceHelper;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class FrontendApiTypeMaker extends AbstractMaker
{
    protected const array EXCLUDED_FIELD_NAMES = ['id', 'domainId', 'locale'];
    protected const array EXCLUDED_ASSOCIATION_NAMES = ['translations', 'translatable', 'domains'];

    protected ?string $selectedEntityClass = null;

    public function __construct(
        protected readonly EntityChoiceHelper $entityChoiceHelper,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Filesystem $filesystem,
        protected readonly string $projectDir,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:frontend-api-type';
    }

    /**
     * {@inheritdoc}
     */
    public static function getCommandDescription(): string
    {
        return 'Create a new frontend API types yaml definition for the selected entity';
    }

    #[Override]
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->selectedEntityClass = $this->entityChoiceHelper->askForEntity($io, 'What is the entity you want to create the API types for?');

        parent::interact($input, $io, $command);
    }

    #[Override]
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $fieldsConfig = $this->getFieldsConfig($this->selectedEntityClass);

        $domainClass = $this->findRelatedClass('Domain');

        if ($domainClass !== null) {
            $fieldsConfig += $this->getFieldsConfig($domainClass);
        }

        $translationClass = $this->findRelatedClass('Translation');

        if ($translationClass !== null) {
            $fieldsConfig += $this->getFieldsConfig($translationClass);
        }

        $entityName = Str::getShortClassName($this->selectedEntityClass);
        $yamlData = [$entityName => ['type' => 'object', 'config' => ['fields' => [...$fieldsConfig]]]];

        $yaml = Yaml::dump($yamlData, 1000);

        $filename = sprintf('%s/config/graphql/types/ModelType/%s/%s.types.yaml', $this->projectDir, $entityName, $entityName);
        $this->filesystem->dumpFile($filename, $yaml);
        $io->writeln(sprintf('File %s created', $filename));
        $this->writeSuccessMessage($io);
    }

    protected function guessApiType(ClassMetadata $metadata, string $fieldName): string
    {
        if ($metadata->isSingleValuedAssociation($fieldName)) {
            $shortClassName = Str::getShortClassName($metadata->getAssociationTargetClass($fieldName));
            $mapping = $metadata->getAssociationMapping($fieldName);
            $isNullable = $mapping['joinColumns'][0]['nullable'] ?? false;

            return $shortClassName . ($isNullable ? '' : '!');
        }

        if ($metadata->isCollectionValuedAssociation($fieldName)) {
            return '[' . Str::getShortClassName($metadata->getAssociationTargetClass($fieldName)) . '!]!';
        }

        $apiType = match ($metadata->getTypeOfField($fieldName)) {
            'guid' => 'Uuid',
            'integer' => 'Int',
            'boolean' => 'Boolean',
            'datetime' => 'DateTime',
            'money' => 'Money',
            default => 'String',
        };

        return $apiType . ($metadata->isNullable($fieldName) ? '' : '!');
    }

    /**
     * @return array{type: string, description: string}
     */
    protected function getFieldDefinition(ClassMetadata $metadata, string $fieldName): array
    {
        return [
            'type' => $this->guessApiType($metadata, $fieldName),
            'description' => 'TODO add the field description here',
        ];
    }

    /**
     * @return array<string, array{type: string, description: string}>
     */
    protected function getFieldsConfig(string $entityClass): array
    {
        $fieldsConfig = [];

        $metadata = $this->entityManager->getClassMetadata($entityClass);

        foreach ($metadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, self::EXCLUDED_FIELD_NAMES, true)) {
                continue;
            }
            $fieldsConfig[$fieldName] = $this->getFieldDefinition($metadata, $fieldName);
        }

        foreach ($metadata->getAssociationNames() as $fieldName) {
            if (in_array($fieldName, [...self::EXCLUDED_ASSOCIATION_NAMES, lcfirst(Str::getShortClassName($this->selectedEntityClass))], true)) {
                continue;
            }
            $fieldsConfig[$fieldName] = $this->getFieldDefinition($metadata, $fieldName);
        }

        return $fieldsConfig;
    }

    protected function findRelatedClass(string $suffix): ?string
    {
        if (class_exists($this->selectedEntityClass . $suffix)) {
            return $this->selectedEntityClass . $suffix;
        }

        if (class_exists(get_parent_class($this->selectedEntityClass) . $suffix)) {
            return get_parent_class($this->selectedEntityClass) . $suffix;
        }

        return null;
    }

    #[Override]
    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    #[Override]
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
