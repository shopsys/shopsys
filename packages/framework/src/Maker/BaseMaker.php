<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Shopsys\FrameworkBundle\Command\EntitiesDumpCommand;
use Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfig;
use Shopsys\FrameworkBundle\Maker\EntityConfig\EntityConfigFactory;
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
     * @param \Shopsys\FrameworkBundle\Maker\BaseMakerDependency $baseMakerDependency
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
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument(self::ENTITY_NAME_ARGUMENT, InputArgument::REQUIRED, 'The entity name (e.g. <fg=yellow>Kitty</>)');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        parent::interact($input, $io, $command);

        $this->entityConfig = $this->entityConfigFactory->createWithEntityNameOnly($input);
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     * @return \Symfony\Bundle\MakerBundle\Util\ClassNameDetails
     */
    protected function createClassNameDetails(Generator $generator): ClassNameDetails
    {
        return $generator->createClassNameDetails(
            $this->entityConfig->entityName,
            preg_replace('/\bApp\\\\/', '', $this->getGeneratedClassNamespace(), 1),
            $this->getGeneratedClassSuffix(),
        );
    }

    /**
     * @return string
     */
    protected function getGeneratedClassNamespace(): string
    {
        return $this->entityConfig->getEntityNamespace();
    }

    /**
     * @param string $fileName
     */
    protected function fixStandards(string $fileName): void
    {
        if (!file_exists($this->kernel->getCacheDir() . '/' . EntitiesDumpCommand::OUTPUT_FILE)) {
            $application = new Application($this->kernel);
            $application->find(EntitiesDumpCommand::getDefaultName())->run(new ArrayInput([]), new NullOutput());
        }

        $process = new Process(['php', 'vendor/bin/ecs', 'check', '--fix', $fileName]);
        $process->mustRun();
    }

    /**
     * @return \Symfony\Bundle\MakerBundle\Util\UseStatementGenerator
     */
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

    /**
     * @return string
     */
    abstract protected function getTemplateName(): string;

    /**
     * @return string[]
     */
    abstract protected function getUseStatements(): array;

    /**
     * @return string[]
     */
    abstract protected function getConstructorDependencies(): array;

    /**
     * @return string
     */
    abstract protected function getGeneratedClassSuffix(): string;

    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
    }
}
