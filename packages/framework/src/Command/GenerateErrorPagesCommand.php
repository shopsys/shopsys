<?php

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateErrorPagesCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:error-page:generate-all';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade
     */
    private $errorPagesFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
     */
    public function __construct(ErrorPagesFacade $errorPagesFacade)
    {
        $this->errorPagesFacade = $errorPagesFacade;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generates all error pages for production.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->errorPagesFacade->generateAllErrorPagesForProduction();

        return CommandResultCodes::RESULT_OK;
    }
}
