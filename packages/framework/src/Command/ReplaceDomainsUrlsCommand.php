<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainUrlService;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplaceDomainsUrlsCommand extends Command
{

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:domains-urls:replace';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\DomainUrlService
     */
    private $domainUrlService;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    public function __construct(
        Domain $domain,
        DomainUrlService $domainUrlService,
        Setting $setting
    ) {
        $this->domain = $domain;
        $this->domainUrlService = $domainUrlService;
        $this->setting = $setting;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Replace domains urls in database by urls in domains config');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainConfigUrl = $domainConfig->getUrl();
            $domainSettingUrl = $this->setting->getForDomain(Setting::BASE_URL, $domainConfig->getId());

            if ($domainConfigUrl !== $domainSettingUrl) {
                $output->writeln(
                    'Domain ' . $domainConfig->getId() . ' URL is not matching URL stored in database.'
                );
                $output->writeln('Replacing domain URL in all string columns...');
                $this->domainUrlService->replaceUrlInStringColumns($domainConfigUrl, $domainSettingUrl);
                $output->writeln('<fg=green>URL successfully replaced.</fg=green>');
            } else {
                $output->writeln('Domain ' . $domainConfig->getId() . ' URL is matching URL stored in database.');
            }
        }
    }
}
