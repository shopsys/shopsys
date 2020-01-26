<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch;

abstract class AbstractIndex
{
    /**
     * @return string
     */
    abstract public function getName(): string;
}
