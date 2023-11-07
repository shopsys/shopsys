<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(name: 'make:extend')]
class MakeExtendCommand extends Command
{

    public function __construct(
        protected readonly ParameterBagInterface $parameterBag,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Extend a class from framework');
        $this->addArgument('className', null, 'Class name');
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $className = $input->getArgument('className');
        if ($className === null) {
            $className = $io->ask('What Class you want to extend?');
        }

        $frameworkSrcDir = $this->parameterBag->get('shopsys.framework.root_dir') . '/src';
        $projectSrcDir = $this->parameterBag->get('kernel.project_dir') . '/src';

        $finder = Finder::create()
            ->files()
            ->ignoreUnreadableDirs()
            ->filter(function (SplFileInfo $file) use ($projectSrcDir) {
                return !file_exists($projectSrcDir . '/' . $file->getRelativePathname());
            })
            ->in($frameworkSrcDir)
            ->name('/.*(' . $className . ').*\.php/');

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRelativePathname();
        }

        if (count($files) === 0) {
            $io->error('No file found');
            return Command::FAILURE;
        }

        $fileName = $io->choice(
            'Which one?',
            $files,
        );

        $extendedFileName = $projectSrcDir . '/' . $fileName;

        $file = $frameworkSrcDir . '/' . $fileName;

        $content = file_get_contents($file);

        preg_match('~namespace\s+Shopsys\\\\FrameworkBundle\\\\([\w\\\\]+);~', $content, $matches);
        $namespace = $matches[1];

        preg_match('~class\s+(\w+)~', $content, $matches);
        $className = $matches[1];

        $newContent = $this->getTemplate($namespace, $className);

        $dir = dirname($extendedFileName);
        $filesystem = new Filesystem();
        $filesystem->mkdir($dir);

        file_put_contents($extendedFileName, $newContent);

        return Command::SUCCESS;
    }

    /**
     * @param string $namespace
     * @param string $className
     * @return string
     */
    private function getTemplate(string $namespace, string $className): string
    {
        return <<<EOF
<?php

declare(strict_types=1);

namespace App\\{$namespace};

use Shopsys\FrameworkBundle\\{$namespace}\\{$className} as Base{$className};

class {$className} extends Base{$className}
{

}
EOF;
    }
}
