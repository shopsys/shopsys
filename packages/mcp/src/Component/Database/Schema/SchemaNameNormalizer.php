<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\OptionallyQualifiedName;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;

class SchemaNameNormalizer
{
    public function __construct(
        protected readonly Connection $mcpConnection,
    ) {
    }

    public function normalizeColumnName(UnqualifiedName $name): string
    {
        return $this->normalizeIdentifier($name->getIdentifier());
    }

    public function normalizeTableName(OptionallyQualifiedName $name): string
    {
        return $this->normalizeIdentifier($name->getUnqualifiedName());
    }

    protected function normalizeIdentifier(Identifier $identifier): string
    {
        return $identifier->toNormalizedValue(
            $this->mcpConnection->getDatabasePlatform()->getUnquotedIdentifierFolding(),
        );
    }
}
