<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting;

use Override;
use Shopsys\HttpSmokeTesting\Auth\AuthInterface;
use Shopsys\HttpSmokeTesting\Auth\NoAuth;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class RequestDataSet implements RequestDataSetConfig
{
    private const DEFAULT_EXPECTED_STATUS_CODE = 200;

    private bool $skipped;

    private ?AuthInterface $auth = null;

    private ?int $expectedStatusCode = null;

    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * @var string[]
     */
    private array $debugNotes;

    /**
     * @var callable[]
     */
    private array $callsDuringTestExecution;

    public function __construct(private string $routeName)
    {
        $this->skipped = false;
        $this->parameters = [];
        $this->debugNotes = [];
        $this->callsDuringTestExecution = [];
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function getAuth(): AuthInterface
    {
        if ($this->auth === null) {
            return new NoAuth();
        }

        return $this->auth;
    }

    public function getExpectedStatusCode(): int
    {
        if ($this->expectedStatusCode === null) {
            return self::DEFAULT_EXPECTED_STATUS_CODE;
        }

        return $this->expectedStatusCode;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string[]
     */
    public function getDebugNotes(): array
    {
        return $this->debugNotes;
    }

    /**
     * @return $this
     */
    public function executeCallsDuringTestExecution(ContainerInterface $container)
    {
        foreach ($this->callsDuringTestExecution as $customization) {
            $customization($this, $container);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function skip()
    {
        $this->skipped = true;

        return $this;
    }

    /**
     * @return $this
     */
    #[Override]
    public function setAuth(AuthInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setExpectedStatusCode(int $code)
    {
        $this->expectedStatusCode = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setParameter(string $name, mixed $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function addDebugNote(string $debugNote)
    {
        $this->debugNotes[] = $debugNote;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function addCallDuringTestExecution(callable $callback)
    {
        $this->callsDuringTestExecution[] = $callback;

        return $this;
    }

    /**
     * Merges values from specified $requestDataSet into this instance.
     *
     * It is used to merge extra RequestDataSet into default RequestDataSet.
     * Values that were not specified in $requestDataSet have no effect on result.
     *
     * @return $this
     */
    public function mergeExtraValuesFrom(self $requestDataSet)
    {
        if ($requestDataSet->auth !== null) {
            $this->setAuth($requestDataSet->getAuth());
        }

        if ($requestDataSet->expectedStatusCode !== null) {
            $this->setExpectedStatusCode($requestDataSet->getExpectedStatusCode());
        }

        foreach ($requestDataSet->getParameters() as $name => $value) {
            $this->setParameter($name, $value);
        }

        foreach ($requestDataSet->getDebugNotes() as $debugNote) {
            $this->addDebugNote($debugNote);
        }

        foreach ($requestDataSet->callsDuringTestExecution as $callback) {
            $this->addCallDuringTestExecution($callback);
        }

        return $this;
    }
}
