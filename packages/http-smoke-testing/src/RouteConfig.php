<?php

namespace Shopsys\HttpSmokeTesting;

interface RouteConfig
{
    /**
     * Skips this route completely. Debug notes will be shown only when executing PHPUnit in verbose mode.
     *
     * @param string|null $debugNote
     * @return $this
     */
    public function skipRoute(?string $debugNote = null);

    /**
     * Allows you to configure default data set of a request for this route. Debug notes will be shown on test failure.
     *
     * @param string|null $debugNote
     */
    public function changeDefaultRequestDataSet(?string $debugNote = null): \Shopsys\HttpSmokeTesting\RequestDataSetConfig;

    /**
     * Adds a new request data set for this route and allows you to configure it. Debug notes will be shown on test failure.
     *
     * Changed configuration in the extra request data set will be merged with default data config overwriting it.
     * Later changes to the default data set will not be discarded.
     *
     * @param string|null $debugNote
     */
    public function addExtraRequestDataSet(?string $debugNote = null): \Shopsys\HttpSmokeTesting\RequestDataSetConfig;
}
