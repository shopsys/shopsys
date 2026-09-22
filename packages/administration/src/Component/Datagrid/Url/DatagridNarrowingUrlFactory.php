<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Url;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * URLs of the current listing with some of the narrowing dropped.
 *
 * The quick search and the filter travel in GET forms named after the datagrid, which are top level query
 * parameters — `GridView::getUrl()` cannot drop them, because everything it is given ends up inside the
 * `g[<gridId>]` namespace of the grid. So the URL is built from the request here instead.
 *
 * Everything is cut out of the submitted query rather than rebuilt from the form data, so that a value the
 * form turned into an object (a picked day, a chosen product) travels on untouched.
 */
class DatagridNarrowingUrlFactory
{
    protected const string GROUPS_KEY = 'groups';

    protected const string RULES_KEY = 'rules';

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly RouterInterface $router,
    ) {
    }

    /**
     * The current listing without the named query parameters.
     *
     * @param string[] $parameterNames Names of the top level query parameters to drop
     */
    public function createUrlWithout(array $parameterNames): string
    {
        $query = $this->getQuery();

        foreach ($parameterNames as $parameterName) {
            unset($query[$parameterName]);
        }

        return $this->createUrl($query);
    }

    /**
     * The current listing without one composed rule of the filter — the group goes with it when that was its
     * last rule, and the whole filter when that was its last group, so the listing never carries an empty filter.
     *
     * @param string $filterParameterName Name of the filter form, which is its query parameter as well
     * @param int|string $groupIndex Index of the group in the submitted filter
     * @param int|string $ruleIndex Index of the rule in the group
     */
    public function createUrlWithoutFilterRule(
        string $filterParameterName,
        int|string $groupIndex,
        int|string $ruleIndex,
    ): string {
        $query = $this->getQuery();
        $filter = $query[$filterParameterName] ?? null;

        if (is_array($filter) === false || is_array($filter[self::GROUPS_KEY][$groupIndex][self::RULES_KEY] ?? null) === false) {
            return $this->createUrl($query);
        }

        unset($filter[self::GROUPS_KEY][$groupIndex][self::RULES_KEY][$ruleIndex]);

        if ($filter[self::GROUPS_KEY][$groupIndex][self::RULES_KEY] === []) {
            unset($filter[self::GROUPS_KEY][$groupIndex]);
        } else {
            $filter[self::GROUPS_KEY][$groupIndex][self::RULES_KEY] = array_values($filter[self::GROUPS_KEY][$groupIndex][self::RULES_KEY]);
        }

        if ($filter[self::GROUPS_KEY] === []) {
            unset($query[$filterParameterName]);
        } else {
            $filter[self::GROUPS_KEY] = array_values($filter[self::GROUPS_KEY]);
            $query[$filterParameterName] = $filter;
        }

        return $this->createUrl($query);
    }

    /**
     * @return mixed[]
     */
    protected function getQuery(): array
    {
        return $this->requestStack->getMainRequest()?->query->all() ?? [];
    }

    /**
     * The listing is back on its first page — records the administrator has not seen yet are never skipped by
     * a page number left over from a narrower listing.
     *
     * @param mixed[] $query
     */
    protected function createUrl(array $query): string
    {
        $request = $this->requestStack->getMainRequest();

        if ($request === null) {
            return '';
        }

        $query[Grid::GET_PARAMETER] = $this->removePages($query[Grid::GET_PARAMETER] ?? []);

        return $this->router->generate(
            $request->attributes->get('_route'),
            array_replace($request->attributes->get('_route_params', []), $query),
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    /**
     * The order and the limit of every grid on the page survive, only the page starts over.
     *
     * @param mixed[] $gridParameters
     * @return mixed[]
     */
    protected function removePages(array $gridParameters): array
    {
        foreach ($gridParameters as $gridId => $parameters) {
            if (is_array($parameters)) {
                unset($gridParameters[$gridId]['page']);
            }
        }

        return $gridParameters;
    }
}
