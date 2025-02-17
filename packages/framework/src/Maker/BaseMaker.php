<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker;

use Shopsys\FrameworkBundle\Command\EntitiesDumpCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

abstract class BaseMaker extends AbstractMaker
{
    protected const string ENTITY_NAMESPACE_PATTERN = 'App\\Model\\%s\\';

    protected string $entityName;

    protected string $entityNamespace;

    protected string $entityFullyQualifiedName;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(protected readonly KernelInterface $kernel)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('name', InputArgument::REQUIRED, 'The entity name (e.g. <fg=yellow>Kitty</>)');
    }

    /**
     * {@inheritdoc}
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $this->entityName = $input->getArgument('name');
        $this->entityNamespace = sprintf(self::ENTITY_NAMESPACE_PATTERN, ucfirst($this->entityName));
        $this->entityFullyQualifiedName = $this->entityNamespace . $this->entityName;

        $classNameDetails = $this->createClassNameDetails($generator);

        $classFile = $generator->generateClass(
            $classNameDetails->getFullName(),
            $this->getTemplateName(),
            [
                'use_statements' => $this->getUseStatementsGenerator(),
                'constructor_dependencies' => $this->getFormattedConstructorDependencies(),
                'entity_name' => $this->entityName,
            ],
        );
        $generator->writeChanges();

        $this->fixStandards($classFile, $input);
        $this->writeSuccessMessage($io);
    }

    /**
     * @param \Symfony\Bundle\MakerBundle\Generator $generator
     * @return \Symfony\Bundle\MakerBundle\Util\ClassNameDetails
     */
    protected function createClassNameDetails(Generator $generator): ClassNameDetails
    {
        return $generator->createClassNameDetails(
            $this->entityName,
            preg_replace('/\bApp\\\\/', '', $this->getGeneratedClassNamespace(), 1),
            $this->getGeneratedClassSuffix(),
        );
    }

    /**
     * @return string
     */
    protected function getGeneratedClassNamespace(): string
    {
        return $this->entityNamespace;
    }

    /**
     * @param string $fileName
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    protected function fixStandards(string $fileName, InputInterface $input): void
    {
        if (!file_exists($this->kernel->getCacheDir() . '/' . EntitiesDumpCommand::OUTPUT_FILE)) {
            $application = new Application($this->kernel);
            $application->find(EntitiesDumpCommand::getDefaultName())->run($input, new NullOutput());
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

        foreach ($this->getConstructorDependencies() as $dependency) {
            $dependencyType = basename(str_replace('\\', '/', $dependency));
            $formattedDependencies[] = sprintf('%s $%s', $dependencyType, lcfirst($dependencyType));
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
