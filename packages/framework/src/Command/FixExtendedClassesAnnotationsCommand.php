<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
use Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory;
use Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class FixExtendedClassesAnnotationsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:extended-classes:fix-annotations';

    /**
     * @var string
     */
    protected $projectRootDirectory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry
     */
    protected $classExtensionRegistry;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer
     */
    protected $annotationsReplacer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap
     */
    protected $annotationsReplacementsMap;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory
     */
    protected $propertyAnnotationsFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory
     */
    protected $methodAnnotationsAdder;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder
     */
    protected $annotationsAdder;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Fixes and adds annotations in project classes to improve static analysis and DX with extended classes. See "help" for more information')
            ->setHelp('What does the command do exactly?
- Replaces the framework with the project annotations in all project files when there exists a project extension of a given framework class.
- Adds @property annotations to project classes when there exists a property in parent class that is extended in the project.
- Adds @method annotations to project classes when there exists a method in parent class that returns an instance of a class that is extended in the project.');
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
        string $projectRootDirectory,
        ClassExtensionRegistry $classExtensionRegistry,
        PropertyAnnotationsFactory $propertyAnnotationsFactory,
        MethodAnnotationsFactory $methodAnnotationsAdder,
        AnnotationsReplacer $annotationsReplacer,
        AnnotationsReplacementsMap $annotationsReplacementsMap,
        AnnotationsAdder $annotationsAdder
    ) {
        parent::__construct();
        $this->projectRootDirectory = $projectRootDirectory;
        $this->classExtensionRegistry = $classExtensionRegistry;
        $this->annotationsReplacer = $annotationsReplacer;
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
        $this->propertyAnnotationsFactory = $propertyAnnotationsFactory;
        $this->methodAnnotationsAdder = $methodAnnotationsAdder;
        $this->annotationsAdder = $annotationsAdder;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->replaceFrameworkWithProjectAnnotations();
        $this->addPropertyAndMethodAnnotationsToProjectClasses();
        $output->writeln('Annotations fixed successfully');
    }

    protected function replaceFrameworkWithProjectAnnotations(): void
    {
        $finder = $this->getFinderForReplacingAnnotations();
        foreach ($finder as $file) {
            $pathname = $file->getPathname();
            $replacedContent = $this->annotationsReplacer->replaceIn(file_get_contents($pathname));
            file_put_contents($pathname, $replacedContent);
        }
    }

    /**
     * @return \Symfony\Component\Finder\Finder
     */
    protected function getFinderForReplacingAnnotations(): Finder
    {
        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->in([
                $this->projectRootDirectory . '/app',
                $this->projectRootDirectory . '/src',
                $this->projectRootDirectory . '/tests',
            ])
            ->name('*.php')
            ->contains($this->annotationsReplacementsMap->getPatternForAny());

        return $finder;
    }

    protected function addPropertyAndMethodAnnotationsToProjectClasses(): void
    {
        $classExtensionMap = $this->classExtensionRegistry->getClassExtensionMap();
        foreach ($classExtensionMap as $frameworkClass => $projectClass) {
            $frameworkClassBetterReflection = ReflectionObject::createFromName($frameworkClass);
            $projectClassBetterReflection = ReflectionObject::createFromName($projectClass);

            $projectClassNecessaryPropertyAnnotationsLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
                $frameworkClassBetterReflection,
                $projectClassBetterReflection
            );
            $projectClassNecessaryMethodAnnotationsLines = $this->methodAnnotationsAdder->getProjectClassNecessaryMethodAnnotationsLines(
                $frameworkClassBetterReflection,
                $projectClassBetterReflection
            );
            $this->annotationsAdder->addAnnotationToClass($projectClassBetterReflection, $projectClassNecessaryPropertyAnnotationsLines . $projectClassNecessaryMethodAnnotationsLines);
        }
    }
}
