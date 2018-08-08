<?php

namespace Shopsys\HttpSmokeTesting;

use Shopsys\HttpSmokeTesting\Auth\AuthInterface;

interface RequestDataSetConfig
{
    /**
     * Sets an authentication method and parameters via an AuthInterface implementation.
     *
     * @see \Shopsys\HttpSmokeTesting\Auth\BasicHttpAuth
     * @see \Shopsys\HttpSmokeTesting\Auth\NoAuth
     *
     * @return $this
     */
    public function setAuth(AuthInterface $auth);

    /**
     * Sets expected HTTP response status code for this request data set.
     *
     * @return $this
     */
    public function setExpectedStatusCode(int $code);

    /**
     * Sets a value of a specified route parameter for this request data set.
     *
     * @param mixed $value
     * @return $this
     */
    public function setParameter(string $name, $value);

    /**
     * Adds a custom debug note for this request data set. Debug notes are shown on test failure for this data set.
     *
     * @return $this
     */
    public function addDebugNote(string $debugNote);

    /**
     * Provided $callback will be called with instance of this and ContainerInterface as arguments.
     *
     * Useful for code that needs to access the same instance of container as the test method.
     *
     * @param callable $callback
     * @return $this
     */
    public function addCallDuringTestExecution(callable $callback);
}
