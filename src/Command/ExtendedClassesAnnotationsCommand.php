<?php

declare(strict_types=1);

namespace App\Command;

use Shopsys\FrameworkBundle\Command\ExtendedClassesAnnotationsCommand as BaseExtendedClassesAnnotationsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExtendedClassesAnnotationsCommand extends BaseExtendedClassesAnnotationsCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:extended-classes:annotations';

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        error_reporting(error_reporting() & ~E_DEPRECATED);

        return parent::execute($input, $output);
    }
}
