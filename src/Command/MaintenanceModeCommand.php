<?php

declare(strict_types=1);

namespace App\Command;

use App\Component\Maintenance\MaintenanceModeSubscriber;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MaintenanceModeCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:maintenance';

    /**
     * @var string
     */
    private const ACTION_ARGUMENT = 'action';

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cacheProvider;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     */
    public function __construct(CacheProvider $cacheProvider)
    {
        parent::__construct();
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setDescription('Enable or disable maintenance mode');
        $this->addArgument(self::ACTION_ARGUMENT, InputArgument::REQUIRED, 'Set action to enable or disable maintenance mode (enable/disable)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);

        if ($input->getArgument(self::ACTION_ARGUMENT) === 'enable') {
            $this->cacheProvider->save(MaintenanceModeSubscriber::MAINTENANCE_CACHE_KEY, true);
            $symfonyStyleIo->note('Maintenance mode was enabled');
        } else {
            if ($this->cacheProvider->contains(MaintenanceModeSubscriber::MAINTENANCE_CACHE_KEY)) {
                $this->cacheProvider->delete(MaintenanceModeSubscriber::MAINTENANCE_CACHE_KEY);
                $symfonyStyleIo->note('Maintenance mode was disabled');
            } else {
                $symfonyStyleIo->note('There is no maintenance mode that should be disabled');
            }
        }

        $symfonyStyleIo->newLine();

        return 0;
    }
}
