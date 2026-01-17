<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AbstractContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:contexts:list',
    description: 'List all registered contexts with their dependencies and details',
)]
class ListContextsCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Context\ContextResolver $contextResolver
     */
    public function __construct(
        private readonly ContextResolverInterface $contextResolver,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Registered Contexts');

        $contexts = $this->contextResolver->getRegisteredContexts();

        $table = new Table($output);
        $table->setHeaders(['Context Class', 'Description', 'Required contexts', 'Identifier']);

        $rows = [];

        foreach ($contexts as $context) {
            $contextClass = get_class($context);
            $requiredContexts = $this->getRequiredContexts($context);

            $rows[] = [
                ReflectionHelper::getShortClassName($contextClass),
                $context->getDescription(),
                count($requiredContexts) > 0 ? implode(', ', $requiredContexts) : '-',
                $context->getIdentifier(),
            ];
        }

        $table->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }

    /**
     * @return array<string>
     */
    private function getRequiredContexts(AbstractContext $context): array
    {
        $requiredContexts = [];

        foreach ($context->getRequiredContexts() as $requiredContextClass) {
            $requiredContexts[] = ReflectionHelper::getShortClassName($requiredContextClass);
        }

        return $requiredContexts;
    }
}
