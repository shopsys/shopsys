<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use LimitIterator;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
use Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory;
use Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'shopsys:extended-classes:annotations',
    description: 'Fixes and adds annotations (or just checks them in dry-run mode) in project classes to improve static analysis and DX with extended classes. See "help" for more information',
)]
class ExtendedClassesAnnotationsCommand extends Command
{
    protected const DRY_RUN = 'dry-run';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                static::DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'By default, the command fixes and adds all the relevant annotations for extended classes. When using this option, it just reports files that need to be fixed.',
            )
            ->setHelp('What does the command do exactly?
- Replaces the shopsys with the project annotations in all project files when there exists a project extension of a given shopsys class.
- Adds @property annotations to project classes when there exists a property in parent class that is extended in the project.
- Adds @method annotations to project classes when there exists a method in parent class that accepts as a parameter or returns an instance of a class that is extended in the project.');
    }

    /**
     * @param string $projectRootDirectory
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry $classExtensionRegistry
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory $propertyAnnotationsFactory
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory $methodAnnotationsAdder
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder $annotationsAdder
     */
    public function __construct(
        protected readonly string $projectRootDirectory,
        protected readonly ClassExtensionRegistry $classExtensionRegistry,
        protected readonly PropertyAnnotationsFactory $propertyAnnotationsFactory,
        protected readonly MethodAnnotationsFactory $methodAnnotationsAdder,
        protected readonly AnnotationsReplacer $annotationsReplacer,
        protected readonly AnnotationsReplacementsMap $annotationsReplacementsMap,
        protected readonly AnnotationsAdder $annotationsAdder,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $isDryRun = (bool)$input->getOption(static::DRY_RUN);
        $filesForReplacingAnnotations = $this->replaceShopsysWithProjectAnnotations($isDryRun);

        if (count($filesForReplacingAnnotations) > 0) {
            if ($isDryRun) {
                $symfonyStyle->error('Following files need fixing annotations:');
            } else {
                $symfonyStyle->note('Annotations were fixed in the following files:');
            }

            $symfonyStyle->listing($filesForReplacingAnnotations);
        }

        $filesForAddingPropertyOrMethodAnnotations = $this->addPropertyAndMethodAnnotationsToProjectClasses($isDryRun);

        if (count($this->methodAnnotationsAdder->getWarnings()) > 0) {
            foreach ($this->methodAnnotationsAdder->getWarnings() as $exception) {
                $symfonyStyle->warning($exception->getMessage());
            }
        }

        if (count($filesForAddingPropertyOrMethodAnnotations) > 0) {
            if ($isDryRun) {
                $symfonyStyle->error('@method or @property annotations need to be added to the following files:');
                $symfonyStyle->listing($filesForAddingPropertyOrMethodAnnotations);
            } else {
                $symfonyStyle->note(
                    array_merge(['@method or @property annotations were added to the following files:'], $filesForAddingPropertyOrMethodAnnotations),
                );
            }
        }

        if (count($filesForReplacingAnnotations) === 0 && count($filesForAddingPropertyOrMethodAnnotations) === 0) {
            $symfonyStyle->success('All good!');

            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $symfonyStyle->note('You can fix the annotations using "annotations-fix" phing command.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param bool $isDryRun
     * @return string[]
     */
    protected function replaceShopsysWithProjectAnnotations(bool $isDryRun): array
    {
        $finder = $this->getFinderForReplacingAnnotations();
        $filesForReplacingAnnotations = [];
        $i = 0;

        do {
            $page = $i * 100;
            $limitIterator = new LimitIterator($finder->getIterator(), $page, 100);

            foreach ($limitIterator as $file) {
                $pathname = $file->getPathname();

                $originalContent = file_get_contents($pathname);
                $replacedContent = $this->annotationsReplacer->replaceIn($originalContent);

                if ($originalContent === $replacedContent) {
                    continue;
                }

                $filesForReplacingAnnotations[] = $file->getRealPath();

                if (!$isDryRun) {
                    file_put_contents($pathname, $replacedContent);
                }
            }

            $i++;
        } while ($page <= $limitIterator->getPosition());

        return $filesForReplacingAnnotations;
    }

    /**
     * @return \Symfony\Component\Finder\Finder
     */
    protected function getFinderForReplacingAnnotations(): Finder
    {
        return Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->in([
                $this->projectRootDirectory . '/app',
                $this->projectRootDirectory . '/src',
            ])
            ->name('*.php');
    }

    /**
     * @param bool $isDryRun
     * @return string[]
     */
    protected function addPropertyAndMethodAnnotationsToProjectClasses(bool $isDryRun): array
    {
        $classExtensionMap = $this->classExtensionRegistry->getClassExtensionMap();
        $filesForAddingPropertyOrMethodAnnotations = [];

        foreach ($classExtensionMap as $shopsysClass => $projectClass) {
            $shopsysClassBetterReflection = ReflectionObject::createFromName($shopsysClass);
            $projectClassBetterReflection = ReflectionObject::createFromName($projectClass);

            if (str_starts_with($projectClass, 'App') === false) {
                continue;
            }

            $projectClassNecessaryPropertyAnnotationsLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
                $shopsysClassBetterReflection,
                $projectClassBetterReflection,
            );
            $projectClassNecessaryMethodAnnotationsLines = $this->methodAnnotationsAdder->getProjectClassNecessaryMethodAnnotationsLines(
                $shopsysClassBetterReflection,
                $projectClassBetterReflection,
            );

            if (!$isDryRun) {
                $this->annotationsAdder->addAnnotationToClass(
                    $projectClassBetterReflection,
                    $projectClassNecessaryPropertyAnnotationsLines . $projectClassNecessaryMethodAnnotationsLines,
                );
            }

            if (
                $projectClassNecessaryPropertyAnnotationsLines !== ''
                || $projectClassNecessaryMethodAnnotationsLines !== ''
            ) {
                $filesForAddingPropertyOrMethodAnnotations[] = $projectClassBetterReflection->getFileName();
            }
        }

        return $filesForAddingPropertyOrMethodAnnotations;
    }
}
