<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class DomainInfoCommand extends Command
{
    protected const ARG_PROPERTY_NAME = 'propertyName';
    protected const ARG_ID = 'domainId';

    protected const OPTION_DEDUPLICATE = 'deduplicate';

    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:domains:info';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        parent::__construct();

        $this->domain = $domain;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Loads and displays domain info.')
            ->addArgument(static::ARG_PROPERTY_NAME, InputArgument::OPTIONAL, 'Property that should be loaded', 'id')
            ->addArgument(static::ARG_ID, InputArgument::OPTIONAL, 'Domain ID (if omitted, the command will output all distinct values)')
            ->addOption(static::OPTION_DEDUPLICATE, 'd', InputOption::VALUE_NONE, 'Return only unique property values (sorted alphabetically)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $domainConfigs = $this->getDomainConfigs($input);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());

            return static::RETURN_CODE_ERROR;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyName = $input->getArgument(static::ARG_PROPERTY_NAME);
        $propertyValues = [];
        foreach ($domainConfigs as $domainConfig) {
            if (!$propertyAccessor->isReadable($domainConfig, $propertyName)) {
                $this->outputPropertyNotAccessible($io, $domainConfig, $propertyName);

                return static::RETURN_CODE_ERROR;
            }

            $propertyValues[] = $propertyAccessor->getValue($domainConfig, $propertyName);
        }

        if ($input->getOption(static::OPTION_DEDUPLICATE)) {
            sort($propertyValues);
            $propertyValues = array_unique($propertyValues);
        }
        $io->writeln(implode("\t", $this->formatPropertyValues($propertyValues)));

        return static::RETURN_CODE_OK;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected function getDomainConfigs(InputInterface $input): array
    {
        $domainConfigs = $this->domain->getAllIncludingDomainConfigsWithoutDataCreated();

        $domainId = $input->getArgument(static::ARG_ID);
        if ($domainId !== null) {
            foreach ($domainConfigs as $domainConfig) {
                if ($domainId === (string)$domainConfig->getId()) {
                    return [$domainConfig];
                }
            }

            throw new \InvalidArgumentException(sprintf('Domain with ID "%s" not found.', $domainId));
        }

        return $domainConfigs;
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $propertyName
     */
    protected function outputPropertyNotAccessible(SymfonyStyle $io, DomainConfig $domainConfig, string $propertyName): void
    {
        $io->error(sprintf('Property "%s" of DomainConfig is not accessible.', $propertyName));

        $propertyExtractor = new ReflectionExtractor();
        $io->writeln('You can access these properties:');
        $io->listing($propertyExtractor->getProperties($domainConfig));
    }

    /**
     * @param mixed[] $propertyValues
     * @return string[]
     */
    protected function formatPropertyValues(array $propertyValues): array
    {
        return array_map(function ($propertyValue) {
            if ($propertyValue === null) {
                return '<options=bold;fg=cyan>NULL</options=bold;fg=cyan>';
            } elseif ($propertyValue === true) {
                return '<options=bold;fg=green>YES</options=bold;fg=green>';
            } elseif ($propertyValue === false) {
                return '<options=bold;fg=red>NO</options=bold;fg=red>';
            } else {
                return $propertyValue;
            }
        }, $propertyValues);
    }
}
