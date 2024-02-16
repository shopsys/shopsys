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
     * @return array
     */
    public function getElasticFieldNamesIndexedByEntityFieldNames(): array;

    /**
     * TODO ještě je varianta to vůbec nedefinovat tady (možná zrušit celý ExportScopeInterface a místo toho vytvořit atributy, kterými bych konfiguroval jednotlivé scopes, ale to mi přijde celkem rozfrcané...
     * TODO každopádně bude fajn mít k dispozici command, kterým si vypíšu všechny dostupné scopes, a scopes dle fieldů
     * @return string[]
     */
    public function getEntityFieldNames(): array;
}
