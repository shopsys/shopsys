<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Doctrine\Common\Collections\ArrayCollection;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class EntityMaker extends BaseMaker
{
    public const string TABLE_NAME_OPTION = 'tableName';
    public const string IS_TRANSLATABLE_OPTION = 'isTranslatable';
    public const string HAS_ID_OPTION = 'hasId';
    public const string HAS_UUID_OPTION = 'hasUuid';

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
            ->addOption(self::HAS_ID_OPTION, null, InputOption::VALUE_REQUIRED, 'Does the entity have an ID?')
            ->addOption(self::HAS_UUID_OPTION, null, InputOption::VALUE_REQUIRED, 'Does the entity have a UUID?');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        $this->entityConfig = $this->entityConfigFactory->create($input, $io);
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
}
