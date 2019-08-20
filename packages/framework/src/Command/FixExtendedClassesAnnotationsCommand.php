<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

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
            ->setDescription('Replaces the framework with the project annotations in all project files when there exists a project extension of a given framework class.');
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
            /**
             * We want to match the FQCN followed by space or an array declaration but want to exclude the substrings.
             * E.g. for "\Shopsys\FrameworkBundle\Model\Product\Product" we do not want to match "\Shopsys\FrameworkBundle\Model\Product\ProductFacade"
             * but we want to match "\Shopsys\FrameworkBundle\Model\Product\Product[]"
             */
            $index = '/(?P<fqcn>\\\\' . preg_quote($frameworkClass, '/') . ')(?!\w)(?P<brackets>(\[\])*)/';
            $value = '$1$2|\\' . $projectClass . '$2';
            $annotationsReplacementsMap[$index] = $value;
        }

        return $annotationsReplacementsMap;
    }
}
