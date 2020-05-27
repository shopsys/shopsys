<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeployPart2LockingCommand extends Command
{
    private const LOCK_CACHE_KEY = 'deployPart2Lock';

    /**
     * @var string
     */
    protected static $defaultName = 'app:deploy-part-2-locking';

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
        $this->setDescription('Create or release lock of deployment part 2');
        $this->addArgument(self::ACTION_ARGUMENT, InputArgument::REQUIRED, 'Set action to lock or unlock deploy part 2');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyleIo = new SymfonyStyle($input, $output);

        if ($input->getArgument(self::ACTION_ARGUMENT) === 'lock') {
            if ($this->cacheProvider->contains(self::LOCK_CACHE_KEY)) {
                $symfonyStyleIo->note('Deploy part 2 lock is already exist');
            } else {
                $this->cacheProvider->save(self::LOCK_CACHE_KEY, true);
                $symfonyStyleIo->note('Deploy part 2 lock has been created');
            }
        } else {
            if ($this->cacheProvider->contains(self::LOCK_CACHE_KEY)) {
                $this->cacheProvider->delete(self::LOCK_CACHE_KEY);
                $symfonyStyleIo->note('Deploy part 2 lock has been released');
            } else {
                $symfonyStyleIo->note('Deploy part 2 lock does not exists. There is nothing to release');
            }
        }

        $symfonyStyleIo->newLine();

        return 0;
    }
}
