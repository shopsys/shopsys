<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\EventDispatcher;
use Doctrine\Migrations\Events;
use Doctrine\Migrations\Metadata\MigrationPlan;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Provider\SchemaDiffProvider;
use Doctrine\Migrations\Query\Query;
use Doctrine\Migrations\Tools\BytesFormatter;
use Doctrine\Migrations\Version\ExecutionResult;
use Doctrine\Migrations\Version\Executor;
use Doctrine\Migrations\Version\State;
use Override;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Heavily inspired by @see \Doctrine\Migrations\Version\DbalExecutor
 */
class MigrationsExecutor implements Executor
{
    /**
     * @var \Doctrine\Migrations\Query\Query[]
     */
    protected array $sqlQueries = [];

    protected SchemaDiffProvider $schemaDiffProvider;

    protected MetadataStorage $metadataStorage;

    protected EventDispatcher $dispatcher;

    public function __construct(
        DependencyFactory $dependencyFactory,
        protected readonly LoggerInterface $logger,
        protected readonly Stopwatch $stopwatch,
        protected readonly ClockInterface $clock,
    ) {
        $this->schemaDiffProvider = $dependencyFactory->getSchemaDiffProvider();
        $this->metadataStorage = $dependencyFactory->getMetadataStorage();
        $this->dispatcher = $dependencyFactory->getEventDispatcher();
    }

    #[Override]
    public function addSql(Query $sqlQuery): void
    {
        $this->sqlQueries[] = $sqlQuery;
    }

    #[Override]
    public function execute(MigrationPlan $plan, MigratorConfiguration $migratorConfiguration): ExecutionResult
    {
        $this->dispatcher->dispatchVersionEvent(
            Events::onMigrationsVersionExecuting,
            $plan,
            $migratorConfiguration,
        );

        $result = new ExecutionResult($plan->getVersion(), $plan->getDirection(), $this->clock->now());
        $this->executeMigration($plan, $result, $migratorConfiguration);
        $result->setSql($this->sqlQueries);

        return $result;
    }

    protected function executeMigration(
        MigrationPlan $plan,
        ExecutionResult $result,
        MigratorConfiguration $configuration,
    ): void {
        $stopwatchEvent = $this->stopwatch->start('execute');

        $migration = $plan->getMigration();
        $direction = $plan->getDirection();
        $version = (string)$plan->getVersion();

        $result->setState(State::PRE);
        $fromSchema = $this->schemaDiffProvider->createFromSchema();
        $migration->{'pre' . ucfirst($direction)}($fromSchema);
        $this->logger->info(sprintf('++ migrating %s', $version));

        $result->setState(State::EXEC);
        $toSchema = $this->schemaDiffProvider->createToSchema($fromSchema);
        $result->setToSchema($toSchema);
        $migration->{$direction}($toSchema);

        foreach ($migration->getSql() as $sqlQuery) {
            $this->addSql($sqlQuery);
        }

        $result->setState(State::POST);
        $migration->{'post' . ucfirst($direction)}($toSchema);

        $stopwatchEvent->stop();
        $periods = $stopwatchEvent->getPeriods();
        $lastPeriod = $periods[count($periods) - 1];

        $result->setTime((float)$lastPeriod->getDuration() / 1000);
        $result->setMemory($lastPeriod->getMemory());

        $this->logger->info(sprintf('Migrated %s (took %fms, used %s memory)', $version, $lastPeriod->getDuration(), BytesFormatter::formatBytes($lastPeriod->getMemory())));

        if (!$configuration->isDryRun()) {
            $this->metadataStorage->complete($result);
        }

        $plan->markAsExecuted($result);
        $result->setState(State::NONE);

        $this->dispatcher->dispatchVersionEvent(
            Events::onMigrationsVersionExecuted,
            $plan,
            $configuration,
        );
    }
}
