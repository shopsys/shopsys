<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\DBAL\Connection;
use Shopsys\FrameworkBundle\Command\Exception\DifferentTimezonesException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTimezonesCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $defaultName = 'shopsys:check-timezones';

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Checks uniformity of PHP and Postgres timezones');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->checkUniformityOfTimezones($output);

        return Command::SUCCESS;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function checkUniformityOfTimezones(OutputInterface $output)
    {
        $output->writeln('Checks uniformity of PHP and Postgres timezones...');

        $phpTimezone = ini_get('date.timezone') === '' ? date_default_timezone_get() : ini_get('date.timezone');

        $stmt = $this->connection->executeQuery('SHOW timezone');

        $postgreSqlTimezone = $stmt->fetchOne();

        if ($postgreSqlTimezone !== $phpTimezone) {
            $message = sprintf(
                'Timezones in PHP and database configuration must be identical.'
                . ' Current settings - PHP:%s, PostgreSQL:%s',
                $phpTimezone,
                $postgreSqlTimezone,
            );

            throw new DifferentTimezonesException($message);
        }

        $output->writeln('Timezones in PHP and database configuration are identical');
    }
}
