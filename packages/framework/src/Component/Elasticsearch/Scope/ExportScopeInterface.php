<?php

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Scope;

use Shopsys\FrameworkBundle\Component\Elasticsearch\ExportableEntityInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

// TODO jak vyřeším export all? Abych se neopakoval, musí existovat X imlpememtací ProductExportFieldInterface pro jednotlivé pole (definované enumem) a tyhle pak využiji v jednotlivých scopech. Pokud mám prázdný scope, potřebuji všechny
#[AutoconfigureTag('shopsys.elasticsearch.export_scope')]
interface ExportScopeInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\ExportPreconditionEnumInterface[]
     */
    public function getPreconditions(): array;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[]
     */
    public function getDependencies(): array;

    public function map(object $object, string $locale, int $domainId): array;
}
