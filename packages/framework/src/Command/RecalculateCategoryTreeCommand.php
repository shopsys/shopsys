<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RecalculateCategoryTreeCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'shopsys:categories:recalculate';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected CategoryFacade $categoryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(CategoryFacade $categoryFacade)
    {
        parent::__construct();

        $this->categoryFacade = $categoryFacade;
    }

    protected function configure(): void
    {
        $this->setDescription('Recalculate left, right and level for category nested set. <options=bold>Sort order of the categories on the same level may change when the tree is invalid.</>');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $style->title('Recalculating category nested set');

        $wasRecalculated = $this->categoryFacade->recalculateNestedSet();

        if ($wasRecalculated) {
            $style->success('Categories were recalculated');
            $style->warning('Sort order of siblings may have changed!');
        } else {
            $style->success('Categories are already fine');
        }

        return CommandResultCodes::RESULT_OK;
    }
}
