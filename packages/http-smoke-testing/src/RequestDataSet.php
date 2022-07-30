<?php

namespace Shopsys\HttpSmokeTesting;

use Shopsys\HttpSmokeTesting\Auth\AuthInterface;
use Shopsys\HttpSmokeTesting\Auth\NoAuth;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestDataSet implements RequestDataSetConfig
{
    private const DEFAULT_EXPECTED_STATUS_CODE = 200;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var bool
     */
    private $skipped;

    /**
     * @var \Shopsys\HttpSmokeTesting\Auth\AuthInterface|null
     */
    private $auth;

    /**
     * @var int|null
     */
    private $expectedStatusCode;

    /**
     * @var array<string, mixed>
     */
    private $parameters;

    /**
     * @var string[]
     */
    private $debugNotes;

    /**
     * @var callable[]
     */
    private $callsDuringTestExecution;

    /**
     * @param string $routeName
     */
    public function __construct(string $routeName)
    {
        $this->routeName = $routeName;
        $this->skipped = false;
        $this->parameters = [];
        $this->debugNotes = [];
        $this->callsDuringTestExecution = [];
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return bool
     */
    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    /**
     * @return \Shopsys\HttpSmokeTesting\Auth\AuthInterface
     */
    public function getAuth(): AuthInterface
    {
        if ($this->auth === null) {
            return new NoAuth();
        }

        return $this->auth;
    }

    /**
     * @return int
     */
    public function getExpectedStatusCode(): int
    {
        if ($this->expectedStatusCode === null) {
            return self::DEFAULT_EXPECTED_STATUS_CODE;
        }

        return $this->expectedStatusCode;
    }

    /**
     * @return array<string, mixed>
     */
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
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return $this
     */
    public function executeCallsDuringTestExecution(ContainerInterface $container): self
    {
        foreach ($this->callsDuringTestExecution as $customization) {
            $customization($this, $container);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function skip(): self
    {
        $this->skipped = true;

        return $this;
    }

    /**
     * @param \Shopsys\HttpSmokeTesting\Auth\AuthInterface $auth
     * @return $this
     */
    public function setAuth(AuthInterface $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setExpectedStatusCode(int $code): self
    {
        $this->expectedStatusCode = $code;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param string $debugNote
     * @return $this
     */
    public function addDebugNote(string $debugNote): self
    {
        $this->debugNotes[] = $debugNote;

        return $this;
    }

    /**
     * Provided $callback will be called with instance of this and ContainerInterface as arguments
     *
     * Useful for code that needs to access the same instance of container as the test method.
     *
     * @param callable $callback
     * @return $this
     */
    public function addCallDuringTestExecution(callable $callback): self
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
     * @param \Shopsys\HttpSmokeTesting\RequestDataSet $requestDataSet
     * @return $this
     */
    public function mergeExtraValuesFrom(self $requestDataSet): self
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
