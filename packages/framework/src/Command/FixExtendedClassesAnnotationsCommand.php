<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry;
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
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('
                Fixes and adds annotations in project classes to improve static analysis and DX with extended classes:
                - Replaces the framework with the project annotations in all project files when there exists a project extension of a given framework class.
                - Adds @property annotations to project classes when there exists a property in parent class that is extended in the project.');
    }

    /**
     * @param string $projectRootDirectory
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\ClassExtensionRegistry $classExtensionRegistry
     */
    public function __construct(string $projectRootDirectory, ClassExtensionRegistry $classExtensionRegistry)
    {
        parent::__construct();
        $this->projectRootDirectory = $projectRootDirectory;
        $this->classExtensionRegistry = $classExtensionRegistry;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->replaceFrameworkWithProjectAnnotations();
        $this->addPropertyAnnotationsToProjectClasses();
        $output->writeln('Annotations fixed successfully');
    }

    protected function replaceFrameworkWithProjectAnnotations(): void
    {
        $finder = $this->getFinderForReplacingAnnotations();
        $annotationsReplacementsMap = $this->getAnnotationsReplacementsMap($this->classExtensionRegistry->getClassExtensionMap());
        foreach ($finder as $fileWithFrameworkAnnotationsToReplace) {
            $pathname = $fileWithFrameworkAnnotationsToReplace->getPathname();
            $content = file_get_contents($pathname);
            $replacedContent = preg_replace(
                array_keys($annotationsReplacementsMap),
                $annotationsReplacementsMap,
                $content
            );
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
            ->contains($this->getRelevantFrameworkAnnotationsPattern());

        return $finder;
    }

    /**
     * @return string
     */
    protected function getRelevantFrameworkAnnotationsPattern(): string
    {
        $frameworkClasses = array_keys($this->classExtensionRegistry->getClassExtensionMap());
        $unescapedRegular = sprintf('/(@var|@param|@return) (\\%s)/', implode('|\\', $frameworkClasses));

        return preg_quote($unescapedRegular, '/');
    }

    /**
     * @param string[] $classExtensionMap
     * @return string[]
     */
    protected function getAnnotationsReplacementsMap(array $classExtensionMap): array
    {
        $annotationsReplacementsMap = [];
        foreach ($classExtensionMap as $frameworkClass => $projectClass) {
            $index = $this->getEscapedFqcnWithLeadingSlashPattern($frameworkClass);
            $value = '$1$2|\\' . $projectClass . '$2';
            $annotationsReplacementsMap[$index] = $value;
        }

        return $annotationsReplacementsMap;
    }

    protected function addPropertyAnnotationsToProjectClasses(): void
    {
        $classExtensionMap = $this->classExtensionRegistry->getClassExtensionMap();
        foreach ($classExtensionMap as $frameworkClass => $projectClass) {
            $frameworkClassBetterReflection = $this->getBetterReflectionClass($frameworkClass);
            foreach ($frameworkClassBetterReflection->getProperties() as $property) {
                $this->addPropertyAnnotationToProjectClassIfNecessary($property, $projectClass, array_keys($classExtensionMap));
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
        $projectClassBetterReflection = $this->getBetterReflectionClass($projectClass);
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
     * @param string $fullyQualifiedClassName
     * @return \Roave\BetterReflection\Reflection\ReflectionClass
     */
    protected function getBetterReflectionClass(string $fullyQualifiedClassName): ReflectionClass
    {
        $projectClassBetterReflection = (new BetterReflection())
            ->classReflector()
            ->reflect($fullyQualifiedClassName);

        return $projectClassBetterReflection;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     */
    protected function addPropertyAnnotationToClass(ReflectionProperty $reflectionProperty, ReflectionClass $projectClassBetterReflection): void
    {
        $replacedTypeForProperty = $this->getReplacedTypeForProperty($reflectionProperty);
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
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @return string
     */
    protected function getReplacedTypeForProperty(ReflectionProperty $reflectionProperty): string
    {
        $propertyType = implode('|', $reflectionProperty->getDocBlockTypeStrings());

        $annotationsReplacementsMap = $this->getAnnotationsReplacementsMap($this->classExtensionRegistry->getClassExtensionMap());
        $replacedPropertyType = preg_replace(
            array_keys($annotationsReplacementsMap),
            $annotationsReplacementsMap,
            $propertyType
        );

        return $replacedPropertyType;
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
}
