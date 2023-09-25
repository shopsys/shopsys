<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class IndexDefinitionModifier
{
    /**
     * @param string $environment
     * @param bool $forceElasticLimit
     */
    public function __construct(
        protected readonly string $environment,
        protected readonly bool $forceElasticLimit,
    ) {
    }

    /**
     * @param array $decodedDefinition
     * @return array
     */
    public function modifyDefinition(array $decodedDefinition): array
    {
        if ($this->environment !== EnvironmentType::PRODUCTION || $this->forceElasticLimit) {
            $decodedDefinition['settings']['index']['number_of_shards'] = 1;
            $decodedDefinition['settings']['index']['number_of_replicas'] = 0;
        }

        return $decodedDefinition;
    }
}
