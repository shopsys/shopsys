<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use DateTimeImmutable;
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

    /**
     * @param \Doctrine\Migrations\DependencyFactory $dependencyFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Symfony\Component\Stopwatch\Stopwatch $stopwatch
     */
    public function __construct(
        DependencyFactory $dependencyFactory,
        protected readonly LoggerInterface $logger,
        protected readonly Stopwatch $stopwatch
    ) {
        $this->schemaDiffProvider = $dependencyFactory->getSchemaDiffProvider();
        $this->metadataStorage = $dependencyFactory->getMetadataStorage();
        $this->dispatcher = $dependencyFactory->getEventDispatcher();
    }

    /**
     * @param \Doctrine\Migrations\Query\Query $sqlQuery
     */
    public function addSql(Query $sqlQuery): void
    {
        $this->sqlQueries[] = $sqlQuery;
    }

    /**
     * @param \Doctrine\Migrations\Metadata\MigrationPlan $plan
     * @param \Doctrine\Migrations\MigratorConfiguration $migratorConfiguration
     * @return \Doctrine\Migrations\Version\ExecutionResult
     */
    public function execute(MigrationPlan $plan, MigratorConfiguration $migratorConfiguration): ExecutionResult
    {
        $this->dispatcher->dispatchVersionEvent(
            Events::onMigrationsVersionExecuting,
            $plan,
            $migratorConfiguration
        );

        $result = new ExecutionResult($plan->getVersion(), $plan->getDirection(), new DateTimeImmutable());
        $this->executeMigration($plan, $result, $migratorConfiguration);
        $result->setSql($this->sqlQueries);

        return $result;
    }

    /**
     * @param \Doctrine\Migrations\Metadata\MigrationPlan $plan
     * @param \Doctrine\Migrations\Version\ExecutionResult $result
     * @param \Doctrine\Migrations\MigratorConfiguration $configuration
     */
    protected function executeMigration(
        MigrationPlan $plan,
        ExecutionResult $result,
        MigratorConfiguration $configuration
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
            $configuration
        );
    }
}
