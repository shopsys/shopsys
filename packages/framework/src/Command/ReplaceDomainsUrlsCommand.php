<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainUrlReplacer;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceDomainsUrlsCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:domains-urls:replace';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\DomainUrlReplacer
     */
    private $domainUrlReplacer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\DomainUrlReplacer $domainUrlReplacer
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        Domain $domain,
        DomainUrlReplacer $domainUrlReplacer,
        Setting $setting
    ) {
        $this->domain = $domain;
        $this->domainUrlReplacer = $domainUrlReplacer;
        $this->setting = $setting;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Replace domains urls in database by urls in domains config');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainConfigUrl = $domainConfig->getUrl();
            $domainSettingUrl = $this->setting->getForDomain(Setting::BASE_URL, $domainConfig->getId());

            if ($domainConfigUrl !== $domainSettingUrl) {
                $output->writeln(
                    'Domain ' . $domainConfig->getId() . ' URL is not matching URL stored in database.'
                );
                $output->writeln('Replacing domain URL in all string columns...');
                $this->domainUrlReplacer->replaceUrlInStringColumns($domainConfigUrl, $domainSettingUrl);
                $output->writeln('<fg=green>URL successfully replaced.</fg=green>');
            } else {
                $output->writeln('Domain ' . $domainConfig->getId() . ' URL is matching URL stored in database.');
            }
        }

        return Command::SUCCESS;
    }
}
