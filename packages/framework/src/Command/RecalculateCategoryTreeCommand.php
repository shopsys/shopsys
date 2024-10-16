<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:categories:recalculate',
    description: 'Recalculate left, right and level for category nested set. <options=bold>Sort order of the categories on the same level may change when the tree is invalid.</>',
)]
class RecalculateCategoryTreeCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(protected readonly CategoryFacade $categoryFacade)
    {
        parent::__construct();
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

        return Command::SUCCESS;
    }
}
