<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDumpCommand extends Command
{
    private const ARG_OUTPUT_FILE = 'outputFile';
    private const OPT_PGDUMP_BIN = 'pgdump-bin';

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:database:dump';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseConnectionCredentialsProvider $databaseConnectionCredentialsProvider
     */
    public function __construct(protected readonly DatabaseConnectionCredentialsProvider $databaseConnectionCredentialsProvider)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Dump database')
            ->addArgument(self::ARG_OUTPUT_FILE, InputArgument::REQUIRED, 'Output SQL file')
            ->addOption(self::OPT_PGDUMP_BIN, null, InputOption::VALUE_OPTIONAL, 'Path to pg_dump binary', 'pg_dump');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // --schema=public option is used in order to dump only "public" schema which contains the application data
        // --no-owner option ensures that the dump can be imported on system with different database username
        $command = sprintf(
            '%s --host=%s --dbname=%s --no-owner --schema=public --username=%s --no-password',
            escapeshellcmd($input->getOption(self::OPT_PGDUMP_BIN)),
            escapeshellarg($this->databaseConnectionCredentialsProvider->getDatabaseHost()),
            escapeshellarg($this->databaseConnectionCredentialsProvider->getDatabaseName()),
            escapeshellarg($this->databaseConnectionCredentialsProvider->getDatabaseUsername()),
        );

        putenv('PGPASSWORD=' . $this->databaseConnectionCredentialsProvider->getDatabasePassword());

        $pipes = [];
        $process = proc_open(
            $command,
            $this->getDescriptorSpec(),
            $pipes,
        );

        [$stdin, $stdout, $stderr] = $pipes;

        $outputFile = $input->getArgument(self::ARG_OUTPUT_FILE);
        $outputFileHandle = fopen($outputFile, 'w');

        while (!feof($stdout)) {
            $line = fgets($stdout);
            fwrite($outputFileHandle, $line);
        }

        $errorMessage = stream_get_contents($stderr);

        if (strlen($errorMessage) > 0) {
            $output->writeln('<error>' . $errorMessage . '</error>');
        } else {
            $output->writeln(sprintf(
                'Database "%s" dumped into file: %s',
                $this->databaseConnectionCredentialsProvider->getDatabaseName(),
                $outputFile,
            ));
        }

        fclose($outputFileHandle);
        fclose($stdin);
        fclose($stdout);
        fclose($stderr);

        return proc_close($process);
    }

    /**
     * @return array
     */
    private function getDescriptorSpec()
    {
        return [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
    }
}
