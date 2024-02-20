<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Scope;

use Webmozart\Assert\Assert;

class ExportScopeInputCasesProvider
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface[]
     */
    protected array $allExportScopeInputs;

    /**
     * @param iterable $allExportScopeInputs
     */
    public function __construct(
        iterable $allExportScopeInputs,
    ) {
        $this->registerScopeInputs($allExportScopeInputs);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInputEnumInterface[]
     */
    public function getCases(): array
    {
        $cases = [];
        foreach ($this->allExportScopeInputs as $exportScopeInput) {
            $cases[] = [...$cases, ...$exportScopeInput::cases()];
        }

        return $cases;
    }

    protected function registerScopeInputs(iterable $allExportScopeInputs): void
    {
        Assert::allIsInstanceOf($allExportScopeInputs, ExportScopeInputEnumInterface::class);

        // TODO ensure project-base implementation has top priority
        foreach ($allExportScopeInputs as $exportScopeInput) {
            $this->allExportScopeInputs[] = $exportScopeInput;
        }
    }
}
