<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\DummyClass;

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
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     */
    public function __construct(
        string $projectRootDirectory,
        ClassExtensionRegistry $classExtensionRegistry,
        AnnotationsReplacer $annotationsReplacer,
        AnnotationsReplacementsMap $annotationsReplacementsMap
    ) {
        parent::__construct();
        $this->projectRootDirectory = $projectRootDirectory;
        $this->classExtensionRegistry = $classExtensionRegistry;
        $this->annotationsReplacer = $annotationsReplacer;
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
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
            $extendedFrameworkClasses = array_keys($classExtensionMap);
            foreach ($frameworkClassBetterReflection->getProperties() as $property) {
                $this->addPropertyAnnotationToProjectClassIfNecessary($property, $projectClass, $extendedFrameworkClasses);
            }
            foreach ($frameworkClassBetterReflection->getMethods() as $method) {
                $this->addMethodAnnotationToProjectClassIfNecessary($method, $projectClass, $extendedFrameworkClasses);
            }
        }
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $projectClass
     * @param string[] $extendedFrameworkClasses
     */
    protected function addPropertyAnnotationToProjectClassIfNecessary(
        ReflectionProperty $reflectionProperty,
        string $projectClass,
        array $extendedFrameworkClasses
    ): void {
        $projectClassBetterReflection = ReflectionObject::createFromName($projectClass);
        foreach ($extendedFrameworkClasses as $extendedFrameworkClass) {
            $isPropertyOfTypeThatIsExtendedInProject = preg_match(
                $this->getEscapedFqcnWithLeadingSlashPattern($extendedFrameworkClass),
                $reflectionProperty->getDocComment()
            );
            if (!$this->isPropertyDeclaredInClass($reflectionProperty->getName(), $projectClassBetterReflection)
                && $isPropertyOfTypeThatIsExtendedInProject
            ) {
                $this->addPropertyAnnotationToClass($reflectionProperty, $projectClassBetterReflection);
            }
        }
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @param string $projectClass
     * @param string[] $extendedFrameworkClasses
     */
    protected function addMethodAnnotationToProjectClassIfNecessary(
        ReflectionMethod $reflectionMethod,
        string $projectClass,
        array $extendedFrameworkClasses
    ): void {
        $projectClassBetterReflection = ReflectionObject::createFromName($projectClass);
        foreach ($extendedFrameworkClasses as $extendedFrameworkClass) {
            foreach ($reflectionMethod->getDocBlockReturnTypes() as $docBlockReturnType) {
                if (!$this->isMethodImplementedInClass($reflectionMethod->getName(), $projectClassBetterReflection)
                    && preg_match($this->getEscapedFqcnWithLeadingSlashPattern($extendedFrameworkClass), (string)$docBlockReturnType)
                ) {
                    $this->addMethodAnnotationToClass($reflectionMethod, $projectClassBetterReflection);
                    break;
                }
            }
        }
    }

    /**
     * We want to match the FQCN followed by space or an array declaration but want to exclude the substrings.
     * E.g. for "\Shopsys\FrameworkBundle\Model\Product\Product" we do not want to match "\Shopsys\FrameworkBundle\Model\Product\ProductFacade"
     * but we want to match "\Shopsys\FrameworkBundle\Model\Product\Product[]"
     *
     * @param string $fullyQualifiedClassName
     * @return string
     */
    protected function getEscapedFqcnWithLeadingSlashPattern(string $fullyQualifiedClassName): string
    {
        return '/(?P<fqcn>\\\\' . preg_quote($fullyQualifiedClassName, '/') . ')(?!\w)(?P<brackets>(\[\])*)/';
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     */
    protected function addPropertyAnnotationToClass(ReflectionProperty $reflectionProperty, ReflectionClass $projectClassBetterReflection): void
    {
        $replacedTypeForProperty = $this->annotationsReplacer->replaceInPropertyType($reflectionProperty);
        $projectClassName = $projectClassBetterReflection->getShortName();
        $projectClassFileName = $projectClassBetterReflection->getFileName();
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        if ($projectClassDocBlock !== '') {
            $this->updateClassAnnotationWithProperty(
                $reflectionProperty,
                $replacedTypeForProperty,
                $projectClassDocBlock,
                $projectClassFileName
            );
        } else {
            $this->addClassAnnotationWithProperty(
                $reflectionProperty,
                $replacedTypeForProperty,
                $projectClassName,
                $projectClassFileName
            );
        }
    }

    /**
     * @param string $propertyName
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     * @return bool
     */
    protected function isPropertyDeclaredInClass(string $propertyName, ReflectionClass $reflectionClass)
    {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        if ($reflectionProperty === null) {
            return false;
        }

        return $reflectionProperty->getDeclaringClass()->getName() === $reflectionClass->getName();
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     */
    protected function addMethodAnnotationToClass(ReflectionMethod $reflectionMethod, ReflectionClass $projectClassBetterReflection): void
    {
        $replacedReturnTypeForMethod = $this->annotationsReplacer->replaceInMethodReturnType($reflectionMethod);
        $projectClassFileName = $projectClassBetterReflection->getFileName();
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        if ($projectClassDocBlock !== '') {
            $this->updateClassAnnotationWithMethod(
                $reflectionMethod,
                $replacedReturnTypeForMethod,
                $projectClassDocBlock,
                $projectClassFileName
            );
        } else {
            $this->addClassAnnotationWithMethod(
                $reflectionMethod,
                $projectClassBetterReflection,
                $replacedReturnTypeForMethod,
                $projectClassFileName
            );
        }
    }

    /**
     * @param string $methodName
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     * @return bool
     */
    protected function isMethodImplementedInClass(string $methodName, ReflectionClass $reflectionClass)
    {
        try {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
            return $reflectionMethod->getDeclaringClass()->getName() === $reflectionClass->getName();
        } catch (\OutOfBoundsException $ex) {
            return false;
        }
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @return string
     */
    protected function getMethodParameterNames(ReflectionMethod $reflectionMethod): string
    {
        $methodParameterNames = [];
        foreach ($reflectionMethod->getParameters() as $methodParameter) {
            $methodParameterNames[] = '$' . $methodParameter->getName();
        }

        return implode(', ', $methodParameterNames);
    }

    /**
     * @param string $fileName
     * @param string $search
     * @param string $replace
     */
    protected function replaceInFile(string $fileName, string $search, string $replace): void
    {
        $fileContent = file_get_contents($fileName);
        $replacedContent = str_replace($search, $replace, $fileContent);
        file_put_contents($fileName, $replacedContent);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $replacedTypeForProperty
     * @param string $projectClassDocBlock
     * @param string $projectClassFileName
     */
    protected function updateClassAnnotationWithProperty(
        ReflectionProperty $reflectionProperty,
        string $replacedTypeForProperty,
        string $projectClassDocBlock,
        string $projectClassFileName
    ): void {
        $propertyAnnotationNewLine = sprintf(
            "* @property %s%s $%s\n",
            $reflectionProperty->isStatic() ? 'static ' : '',
            $replacedTypeForProperty,
            $reflectionProperty->getName()
        );
        if (strpos($projectClassDocBlock, $propertyAnnotationNewLine) === false) {
            $extendedDocBlock = str_replace('*/', $propertyAnnotationNewLine . ' */', $projectClassDocBlock);
            $this->replaceInFile($projectClassFileName, $projectClassDocBlock, $extendedDocBlock);
        }
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $replacedTypeForProperty
     * @param string $projectClassShortName
     * @param string $projectClassFileName
     */
    protected function addClassAnnotationWithProperty(
        ReflectionProperty $reflectionProperty,
        string $replacedTypeForProperty,
        string $projectClassShortName,
        string $projectClassFileName
    ): void {
        $replacement = sprintf(
            "/**\n * @property %s%s $%s\n */\nclass %s",
            $reflectionProperty->isStatic() ? 'static ' : '',
            $replacedTypeForProperty,
            $reflectionProperty->getName(),
            $projectClassShortName
        );
        $this->replaceInFile($projectClassFileName, 'class ' . $projectClassShortName, $replacement);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @param string $replacedReturnTypeForMethod
     * @param string $projectClassFileName
     */
    protected function addClassAnnotationWithMethod(
        ReflectionMethod $reflectionMethod,
        ReflectionClass $projectClassBetterReflection,
        string $replacedReturnTypeForMethod,
        string $projectClassFileName
    ): void {
        $projectClassShortName = $projectClassBetterReflection->getShortName();
        $replacement = sprintf(
            "/**\n * @method %s%s %s(%s)\n */\nclass %s",
            $reflectionMethod->isStatic() ? 'static ' : '',
            $replacedReturnTypeForMethod,
            $reflectionMethod->getName(),
            $this->getMethodParameterNames($reflectionMethod),
            $projectClassShortName
        );
        $this->replaceInFile($projectClassFileName, 'class ' . $projectClassShortName, $replacement);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @param string $replacedReturnTypeForMethod
     * @param string $projectClassDocBlock
     * @param string $projectClassFileName
     */
    protected function updateClassAnnotationWithMethod(
        ReflectionMethod $reflectionMethod,
        string $replacedReturnTypeForMethod,
        string $projectClassDocBlock,
        string $projectClassFileName
    ): void {
        $newMethodAnnotationLine = sprintf(
            "* @method %s%s %s(%s)\n",
            $reflectionMethod->isStatic() ? 'static ' : '',
            $replacedReturnTypeForMethod,
            $reflectionMethod->getName(),
            $this->getMethodParameterNames($reflectionMethod)
        );
        if (strpos($projectClassDocBlock, $newMethodAnnotationLine) === false) {
            $extendedDocBlock = str_replace('*/', $newMethodAnnotationLine . ' */', $projectClassDocBlock);
            $this->replaceInFile($projectClassFileName, $projectClassDocBlock, $extendedDocBlock);
        }
    }
}
