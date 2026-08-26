<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use LimitIterator;
use Override;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
use Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory;
use Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory;
use Shopsys\FrameworkBundle\Component\ClassExtension\StaleAnnotationsRemover;
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
    #[Override]
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
- Adds @method annotations to project classes when there exists a method in parent class that accepts as a parameter or returns an instance of a class that is extended in the project.
- Removes stale @property and @method annotations from project classes when the referenced method or property no longer exists in the parent class.');
    }

    public function __construct(
        protected readonly string $projectRootDirectory,
        protected readonly ClassExtensionRegistry $classExtensionRegistry,
        protected readonly PropertyAnnotationsFactory $propertyAnnotationsFactory,
        protected readonly MethodAnnotationsFactory $methodAnnotationsAdder,
        protected readonly AnnotationsReplacer $annotationsReplacer,
        protected readonly AnnotationsAdder $annotationsAdder,
        protected readonly StaleAnnotationsRemover $staleAnnotationsRemover,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
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

        $filesWithRemovedStaleAnnotations = $this->removeStalePropertyAndMethodAnnotationsFromProjectClasses($isDryRun);

        if (count($filesWithRemovedStaleAnnotations) > 0) {
            if ($isDryRun) {
                $symfonyStyle->error('Stale @method or @property annotations need to be removed from the following files:');
                $symfonyStyle->listing($filesWithRemovedStaleAnnotations);
            } else {
                $symfonyStyle->note(
                    array_merge(['Stale @method or @property annotations were removed from the following files:'], $filesWithRemovedStaleAnnotations),
                );
            }
        }

        $hasChanges = count($filesForReplacingAnnotations) > 0
            || count($filesForAddingPropertyOrMethodAnnotations) > 0
            || count($filesWithRemovedStaleAnnotations) > 0;

        if (!$hasChanges) {
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

    /**
     * @return string[]
     */
    protected function removeStalePropertyAndMethodAnnotationsFromProjectClasses(bool $isDryRun): array
    {
        $classExtensionMap = $this->classExtensionRegistry->getClassExtensionMap();
        $filesWithRemovedAnnotations = [];

        foreach ($classExtensionMap as $shopsysClass => $projectClass) {
            if (str_starts_with($projectClass, 'App') === false) {
                continue;
            }

            $shopsysClassBetterReflection = ReflectionObject::createFromName($shopsysClass);
            $projectClassBetterReflection = ReflectionObject::createFromName($projectClass);

            $staleLines = $this->staleAnnotationsRemover->getStaleAnnotationLines(
                $shopsysClassBetterReflection,
                $projectClassBetterReflection,
            );

            if (count($staleLines) <= 0) {
                continue;
            }

            $filesWithRemovedAnnotations[] = $projectClassBetterReflection->getFileName();

            if (!$isDryRun) {
                $this->staleAnnotationsRemover->removeStaleAnnotationsFromClass(
                    $shopsysClassBetterReflection,
                    $projectClassBetterReflection,
                );
            }
        }

        return $filesWithRemovedAnnotations;
    }
}
