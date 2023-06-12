<?php

declare(strict_types=1);

namespace App\Command;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainUrlReplacer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceCdnDomainUrlCommand extends Command
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = 'shopsys:cdn-domain-url:replace';

    /**
     * @param string $cdnDomainUrl
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainUrlReplacer $domainUrlReplacer
     */
    public function __construct(
        private readonly string $cdnDomainUrl,
        private readonly Domain $domain,
        private readonly DomainUrlReplacer $domainUrlReplacer,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Replace "content" and "public" folders url in all database text columns by corresponding CDN url');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $trimmedCdnDomainUrl = trim($this->cdnDomainUrl, '/');
        if ($trimmedCdnDomainUrl === '') {
            $output->writeln('No CDN domain set, nothing to replace.');
            return Command::SUCCESS;
        }
        $contentDirectory = '/content';
        $publicDirectory = '/public';
        $cdnContentUrl = $trimmedCdnDomainUrl . $contentDirectory;
        $cdnPublicUrl = $trimmedCdnDomainUrl . $publicDirectory;
        foreach ($this->domain->getAll() as $domainConfig) {
            $trimmedDomainUrl = trim($domainConfig->getUrl(), '/');

            $domainContentUrl = $trimmedDomainUrl . $contentDirectory;
            $this->domainUrlReplacer->replaceUrlInStringColumns($cdnContentUrl, $domainContentUrl);
            $output->writeln(sprintf('Replaced %s with %s in all string columns', $domainContentUrl, $cdnContentUrl));

            $domainPublicUrl = $trimmedDomainUrl . $publicDirectory;
            $this->domainUrlReplacer->replaceUrlInStringColumns($cdnPublicUrl, $domainPublicUrl);
            $output->writeln(sprintf('Replaced %s with %s in all string columns', $domainPublicUrl, $cdnPublicUrl));
        }
        return Command::SUCCESS;
    }
}
