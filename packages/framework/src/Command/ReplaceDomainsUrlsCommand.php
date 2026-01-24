<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\DomainUrlReplacer;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Messenger\ArticleExportMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:domains-urls:replace',
    description: 'Replace domains urls in database by urls in domains config and then marks articles, blog articles and scope (`SCOPE_DOMAIN_URL`) of products to be exported to Elasticsearch',
)]
class ReplaceDomainsUrlsCommand extends Command
{
    public function __construct(
        private readonly Domain $domain,
        private readonly DomainUrlReplacer $domainUrlReplacer,
        private readonly Setting $setting,
        private readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        private readonly BlogArticleExportQueueFacade $blogArticleExportQueueFacade,
        private readonly ArticleExportMessageDispatcher $articleExportMessageDispatcher,
        private readonly ArticleFacade $articleFacade,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dispatchProducts = false;

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainConfigUrl = $domainConfig->getUrl();
            $domainSettingUrl = $this->setting->getForDomain(Setting::BASE_URL, $domainConfig->getId());

            if ($domainConfigUrl !== $domainSettingUrl) {
                $output->writeln(
                    'Domain ' . $domainConfig->getId() . ' URL is not matching URL stored in database.',
                );
                $output->writeln('Replacing domain URL in all string columns...');
                $this->domainUrlReplacer->replaceUrlInStringColumns($domainConfigUrl, $domainSettingUrl);

                $this->blogArticleExportQueueFacade->addAll($domainConfig->getId());
                $this->articleExportMessageDispatcher->dispatchArticleExportMessages($this->articleFacade->getAllIdsByDomainId($domainConfig->getId()), $domainConfig->getId());
                $dispatchProducts = true;

                $output->writeln('<fg=green>URL successfully replaced.</fg=green>');
            } else {
                $output->writeln('Domain ' . $domainConfig->getId() . ' URL is matching URL stored in database.');
            }
        }

        if ($dispatchProducts === true) {
            $this->productRecalculationDispatcher->dispatchAllProducts([ProductExportScopeConfig::SCOPE_DOMAIN_URL]);
        }

        return Command::SUCCESS;
    }
}
