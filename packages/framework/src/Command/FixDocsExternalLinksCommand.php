<?php

namespace Shopsys\FrameworkBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class FixDocsExternalLinksCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:documentation:fix-links';

    protected function configure()
    {
        $this
            ->setDescription('')
            ->setHelp('');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = '9.0';
        $ignoredLinks = [
            'UPGRADE\.md',
            'CHANGELOG\.md',
            'README\.md',
        ];

        $finder = new Finder();
        // @todo parametrize path
        $pattern = '~(https?:\/\/github.com\/shopsys\/shopsys\/(blob|tree)\/)(?!v|' . preg_quote($basePath, '~') . ')[^\/]*\/(?!(' . implode('|', $ignoredLinks) . '))~i';

        $finder
            ->files()
            ->in(__DIR__ . '/../../../../docs')
            ->name('*.md')
            ->contains($pattern);

        foreach ($finder as $file) {
            $content = $file->getContents();

            $a = preg_replace($pattern, '${1}' . $basePath . '/', $content);

            file_put_contents($file->getRealPath(), $a);
        }
    }
}
