<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\Type;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\QueryComplexity;
use Shopsys\FrontendApiBundle\Component\GraphQL\ConnectionAwareQueryComplexity;
use Shopsys\FrontendApiBundle\Model\ScalarType\StringType;

class GraphqlConfigurator
{
    public function __construct(
        protected readonly int $maxQueryComplexity,
    ) {
    }

    public function applyExtraConfiguration(): void
    {
        $this->overrideStandardTypes();
        $this->overrideQueryComplexityRule();
    }

    protected function overrideStandardTypes(): void
    {
        $types = Type::getStandardTypes();

        // Prevents multiple overriding in tests as standard types stays overridden even on new booted kernel
        if ($types[Type::STRING] instanceof StringType) {
            return;
        }

        GraphQL::overrideStandardTypes([
            Type::STRING => new StringType(),
        ]);
    }

    protected function overrideQueryComplexityRule(): void
    {
        $queryComplexityRule = DocumentValidator::getRule(QueryComplexity::class);

        if (
            $queryComplexityRule instanceof ConnectionAwareQueryComplexity
            && $queryComplexityRule->getMaxQueryComplexity() === $this->maxQueryComplexity
        ) {
            return;
        }

        DocumentValidator::addRule(new ConnectionAwareQueryComplexity($this->maxQueryComplexity));
    }
}
