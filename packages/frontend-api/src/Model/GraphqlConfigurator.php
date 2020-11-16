<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\Type;
use Shopsys\FrontendApiBundle\Model\ScalarType\StringType;

class GraphqlConfigurator
{
    public function applyExtraConfiguration(): void
    {
        $this->overrideStandardTypes();
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
}
