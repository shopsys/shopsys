<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\Batch;

use Symfony\Component\Messenger\Handler\Acknowledger;

trait BatchHandlerWithTimeLimitTrait
{
    /**
     * @var array<int, array{0: object, 1: \Symfony\Component\Messenger\Handler\Acknowledger}>
     */
    protected array $jobs = [];

    protected ?int $batchStartedAt = null;

    /**
     * @param bool $force
     */
    public function flush(bool $force): void
    {
        if (!$force && !$this->shouldFlush()) {
            return;
        }

        if ($this->jobs === []) {
            $this->batchStartedAt = null;

            return;
        }

        $jobs = $this->jobs;
        $this->jobs = [];
        $this->batchStartedAt = null;
        $this->process($jobs);
    }

    /**
     * @param object $message
     * @param \Symfony\Component\Messenger\Handler\Acknowledger|null $ack
     * @return mixed
     */
    protected function handle(object $message, ?Acknowledger $ack): mixed
    {
        if ($ack === null) {
            $ack = new Acknowledger(get_debug_type($this));
            $this->jobs[] = [$message, $ack];
            $this->batchStartedAt ??= hrtime(true);
            $this->flush(true);

            return $ack->getResult();
        }

        $this->jobs[] = [$message, $ack];
        $this->batchStartedAt ??= hrtime(true);

        if (!$this->shouldFlush()) {
            return count($this->jobs);
        }

        $this->flush(true);

        return 0;
    }

    /**
     * @return bool
     */
    /**
     * @return bool
     */
    protected function shouldFlush(): bool
    {
        if ($this->jobs === []) {
            return false;
        }

        if ($this->getBatchSize() <= count($this->jobs)) {
            return true;
        }

        if ($this->batchStartedAt === null) {
            return false;
        }

        $maxWaitMilliseconds = $this->getMaxWaitMilliseconds();

        if ($maxWaitMilliseconds <= 0) {
            return false;
        }

        return hrtime(true) - $this->batchStartedAt >= $maxWaitMilliseconds * 1000000;
    }

    /**
     * @return int
     */
    protected function getBatchSize(): int
    {
        return 10;
    }

    /**
     * @return int
     */
    protected function getMaxWaitMilliseconds(): int
    {
        return 2000;
    }

    /**
     * Completes the jobs in the list.
     *
     * @param list<array{0: object, 1: \Symfony\Component\Messenger\Handler\Acknowledger}> $jobs
     */
    abstract protected function process(array $jobs): void;
}
