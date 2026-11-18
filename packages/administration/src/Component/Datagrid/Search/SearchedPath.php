<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * A path the quick search looks for the text in.
 */
final readonly class SearchedPath
{
    /**
     * @param string $label Label of the searched field in the placeholder of the quick search
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum|null $valueType Null when the adapter does not describe its paths
     */
    public function __construct(
        public string $path,
        public string $label,
        public ?PathValueTypeEnum $valueType,
    ) {
    }
}
