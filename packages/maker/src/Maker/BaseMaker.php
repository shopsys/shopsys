<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Maker;

use Override;
use Shopsys\FrameworkBundle\Command\EntitiesDumpCommand;
use Shopsys\MakerBundle\EntityConfig\EntityConfig;
use Shopsys\MakerBundle\EntityConfig\EntityConfigFactory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Service\Attribute\Required;

abstract class BaseMaker extends AbstractMaker
{
    public const string ENTITY_NAME_ARGUMENT = 'entityName';

    protected EntityConfig $entityConfig;

    protected EntityConfigFactory $entityConfigFactory;

    protected KernelInterface $kernel;

    /**
     * Set mandatory dependencies with setter injection to keep constructor clean for extending classes
     *
     * @internal This method is public only for the purpose of setter injection
     */
    #[Required]
    public function setDependencies(BaseMakerDependency $baseMakerDependency): void
    {
        $this->kernel = $baseMakerDependency->kernel;
        $this->entityConfigFactory = $baseMakerDependency->entityConfigFactory;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument(self::ENTITY_NAME_ARGUMENT, InputArgument::REQUIRED, 'The entity name (e.g. <fg=yellow>Kitty</>)');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        parent::interact($input, $io, $command);

        $this->entityConfig = $this->entityConfigFactory->createWithEntityNameOnly($input);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $classNameDetails = $this->createClassNameDetails($generator);

        $classFile = $generator->generateClass(
            $classNameDetails->getFullName(),
            $this->getTemplateName(),
            [
                'use_statements' => $this->getUseStatementsGenerator(),
                'constructor_dependencies' => $this->getFormattedConstructorDependencies(),
                'entity_config' => $this->entityConfig,
            ],
        );
        $generator->writeChanges();

        $this->fixStandards($classFile);
        $this->writeSuccessMessage($io);
    }

    protected function createClassNameDetails(Generator $generator): ClassNameDetails
    {
        return $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            $this->getGeneratedClassNamespaceWithoutAppPrefix(),
            $this->getGeneratedClassSuffix(),
        );
    }

    protected function getGeneratedClassNamespace(): string
    {
        return $this->entityConfig->getEntityNamespace();
    }

    protected function getGeneratedClassNamespaceWithoutAppPrefix(): string
    {
        return preg_replace('/\bApp\\\\/', '', $this->getGeneratedClassNamespace(), 1);
    }

    protected function fixStandards(string $fileName, bool $forceEntitiesDump = false): void
    {
        if ($forceEntitiesDump || !file_exists($this->kernel->getCacheDir() . '/' . EntitiesDumpCommand::OUTPUT_FILE)) {
            $application = new Application($this->kernel);
            $application->find(EntitiesDumpCommand::getDefaultName())->run(new ArrayInput([]), new NullOutput());
        }

        $process = new Process(['php', 'vendor/bin/ecs', 'check', '--fix', $fileName]);
        $process->mustRun();
    }

    protected function getUseStatementsGenerator(): UseStatementGenerator
    {
        return new UseStatementGenerator([
            ...array_values($this->getConstructorDependencies()),
            ...$this->getUseStatements(),
        ]);
    }

    /**
     * @return string[]
     */
    protected function getFormattedConstructorDependencies(): array
    {
        $formattedDependencies = [];

        foreach ($this->getConstructorDependencies() as $key => $dependency) {
            $dependencyType = basename(str_replace('\\', '/', $dependency));
            $dependencyName = is_string($key) ? $key : lcfirst($dependencyType);
            $formattedDependencies[] = sprintf('%s $%s', $dependencyType, $dependencyName);
        }

        return $formattedDependencies;
    }

    abstract protected function getTemplateName(): string;

    /**
     * @return string[]
     */
    abstract protected function getUseStatements(): array;

    /**
     * @return string[]
     */
    abstract protected function getConstructorDependencies(): array;

    abstract protected function getGeneratedClassSuffix(): string;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
}
