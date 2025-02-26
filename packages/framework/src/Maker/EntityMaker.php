<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Override;
use Prezent\Doctrine\Translatable\Entity\AbstractTranslation;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Maker\EntityConfig\EntityFieldsConfigurator;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class EntityMaker extends BaseMaker
{
    public const string TABLE_NAME_OPTION = 'tableName';
    public const string IS_TRANSLATABLE_OPTION = 'isTranslatable';
    public const string IS_MULTI_DOMAIN_OPTION = 'isMultiDomain';
    public const string HAS_ID_OPTION = 'hasId';
    public const string HAS_UUID_OPTION = 'hasUuid';

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityFieldsConfigurator $entityFieldsConfigurator
     */
    public function __construct(
        protected readonly EntityFieldsConfigurator $entityFieldsConfigurator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getCommandName(): string
    {
        return 'make:shopsys:entity';
    }

    /**
     * {@inheritdoc}
     */
    public static function getCommandDescription(): string
    {
        return 'Create a new entity class';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        parent::configureCommand($command, $inputConfig);

        $command
            ->addOption(self::TABLE_NAME_OPTION, null, InputOption::VALUE_REQUIRED, 'The database table name')
            ->addOption(self::IS_TRANSLATABLE_OPTION, null, InputOption::VALUE_REQUIRED, 'Is the entity translatable?')
            ->addOption(self::IS_MULTI_DOMAIN_OPTION, null, InputOption::VALUE_REQUIRED, 'Is the entity multi domain?')
            ->addOption(self::HAS_ID_OPTION, null, InputOption::VALUE_REQUIRED, 'Does the entity have an ID?')
            ->addOption(self::HAS_UUID_OPTION, null, InputOption::VALUE_REQUIRED, 'Does the entity have a UUID?');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Bundle\MakerBundle\ConsoleStyle $io
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $this->createEntityClass($generator);
        $this->createEntityDataClass($generator);
        $this->createEntityDataFactoryClass($generator);

        if ($this->entityConfig->isTranslatable) {
            $this->createEntityTranslationClass($generator);
        }

        if ($this->entityConfig->isMultiDomain) {
            $this->createEntityDomainClass($generator);
            $this->createEntityDomainNotFoundExceptionClass($generator);
        }

        $forceEntitiesDump = true;

        foreach ($generator->getGeneratedFiles() as $file) {
            $this->fixStandards($file, $forceEntitiesDump);
            $forceEntitiesDump = false;
        }
        $this->writeSuccessMessage($io);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->entityConfig = $this->entityConfigFactory->create($input, $io);
        $this->entityFieldsConfigurator->configureEntityFields($this->entityConfig, $io);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getTemplateName(): string
    {
        return __DIR__ . '/templates/Entity.tpl.php';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getGeneratedClassSuffix(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getUseStatements(): array
    {
        $useStatements = [
            'Doctrine\ORM\Mapping as ORM',
        ];

        if ($this->entityConfig->isTranslatable) {
            $useStatements[] = AbstractTranslatableEntity::class;
            $useStatements[] = ArrayCollection::class;
            $useStatements[] = 'Prezent\Doctrine\Translatable\Annotation as Prezent';
        }

        if ($this->entityConfig->isMultiDomain) {
            $useStatements[] = ArrayCollection::class;
            $useStatements[] = Collection::class;
            $useStatements[] = $this->entityConfig->getEntityNamespace() . 'Exception\\' . $this->entityConfig->entityName . 'DomainNotFoundException';
        }

        if ($this->entityConfig->hasUuid) {
            $useStatements[] = Uuid::class;
        }

        return $useStatements;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getConstructorDependencies(): array
    {
        return [];
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    protected function createEntityClass(Generator $generator): void
    {
        $classNameDetails = $this->createClassNameDetails($generator);

        $generator->generateClass(
            $classNameDetails->getFullName(),
            $this->getTemplateName(),
            [
                'use_statements' => $this->getUseStatementsGenerator(),
                'constructor_dependencies' => $this->getFormattedConstructorDependencies(),
                'entity_config' => $this->entityConfig,
            ],
        );
        $generator->writeChanges();
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    protected function createEntityDataClass(Generator $generator): void
    {
        $classNameDetails = $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            $this->getGeneratedClassNamespaceWithoutAppPrefix(),
            'Data',
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/EntityData.tpl.php',
            [
                'entity_config' => $this->entityConfig,
            ],
        );
        $generator->writeChanges();
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    protected function createEntityDataFactoryClass(Generator $generator): void
    {
        $classNameDetails = $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            $this->getGeneratedClassNamespaceWithoutAppPrefix(),
            'DataFactory',
        );

        $useStatements = [];

        if ($this->entityConfig->isTranslatable || $this->entityConfig->isMultiDomain) {
            $useStatements[] = Domain::class;
        }

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/EntityDataFactory.tpl.php',
            [
                'entity_config' => $this->entityConfig,
                'use_statements' => new UseStatementGenerator($useStatements),
            ],
        );
        $generator->writeChanges();
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    protected function createEntityTranslationClass(Generator $generator): void
    {
        $classNameDetails = $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            $this->getGeneratedClassNamespaceWithoutAppPrefix(),
            'Translation',
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/EntityTranslation.tpl.php',
            [
                'entity_config' => $this->entityConfig,
                'use_statements' => new UseStatementGenerator([
                    'Doctrine\ORM\Mapping as ORM',
                    'Prezent\Doctrine\Translatable\Annotation as Prezent',
                    AbstractTranslation::class,
                ]),
            ],
        );
        $generator->writeChanges();
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    protected function createEntityDomainClass(Generator $generator): void
    {
        $classNameDetails = $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            $this->getGeneratedClassNamespaceWithoutAppPrefix(),
            'Domain',
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/EntityDomain.tpl.php',
            [
                'entity_config' => $this->entityConfig,
                'use_statements' => new UseStatementGenerator([
                    'Doctrine\ORM\Mapping as ORM',
                ]),
            ],
        );
        $generator->writeChanges();
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     */
    protected function createEntityDomainNotFoundExceptionClass(Generator $generator): void
    {
        $classNameDetails = $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            $this->getGeneratedClassNamespaceWithoutAppPrefix() . 'Exception',
            'DomainNotFoundException',
        );

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/templates/EntityDomainNotFoundException.tpl.php',
            [
                'entity_config' => $this->entityConfig,
                'use_statements' => new UseStatementGenerator([
                    Exception::class,
                ]),
            ],
        );
        $generator->writeChanges();
    }
}
