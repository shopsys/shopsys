<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use PhpAmqpLib\Connection\AbstractConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckRabbitMqCommand extends Command
{
    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;

    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:rabbitmq:check-availability';

    /**
     * @var \PhpAmqpLib\Connection\AbstractConnection
     */
    protected $rabbitMqConnection;

    /**
     * @param \PhpAmqpLib\Connection\AbstractConnection $rabbitMqConnection
     */
    public function __construct(AbstractConnection $rabbitMqConnection)
    {
        parent::__construct();

        $this->rabbitMqConnection = $rabbitMqConnection;
    }

    protected function configure()
    {
        $this->setDescription('Checks availability of RabbitMQ');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Checks availability of RabbitMQ...');

        try {
            $this->rabbitMqConnection->reconnect();
        } catch (\PhpAmqpLib\Exception\AMQPExceptionInterface $e) {
            $io->error('RabbitMQ is not available.');

            return static::RETURN_CODE_ERROR;
        }

        if ($this->rabbitMqConnection->isConnected()) {
            $io->success('RabbitMQ is available.');

            return static::RETURN_CODE_OK;
        }

        $io->error('RabbitMQ is not available.');

        return static::RETURN_CODE_ERROR;
    }
}
