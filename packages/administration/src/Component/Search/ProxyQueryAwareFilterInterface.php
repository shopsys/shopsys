<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;

/**
 * A filter that resolves field paths (dot notation, translations) via the list's ProxyQuery.
 * The applier injects the ProxyQuery before calling extendQueryBuilder().
 */
interface ProxyQueryAwareFilterInterface extends FilterInterface
{
    public function setProxyQuery(ProxyQuery $proxyQuery): void;
}
