<?php

namespace Shopsys\FrameworkBundle\Model\Product\Export\Preconditions;

use Shopsys\FrameworkBundle\Component\Elasticsearch\ExportPreconditionEnumInterface;

enum ProductExportPreconditionsEnum: string implements ExportPreconditionEnumInterface
{
    case VISIBILITY_RECALCULATION = 'visibility_recalculation';
    case SELLING_DENIED_RECALCULATION = 'selling_denied_recalculation';
}
