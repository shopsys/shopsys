<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * Tells which filters would tell what a hidden field holds:
 *
 * - a filter under the filter name of the hidden field;
 * - a filter over a property of the hidden field, a path under it, or the association it lies under.
 *
 * The paths are compared twice:
 *
 * - as written, so `translations.name` lies under the hidden association `translations`;
 * - without the `translations` segments, so `translations.name` meets the hidden translated field `name`
 *   and `status.translations` lies above the hidden `status.name`.
 *
 * The paths are compared as text. A filter reaching the column of a hidden field by another route
 * (`detail.product.name`) is not recognized, so do not declare one.
 */
final readonly class HiddenFieldFilterGuard
{
    /**
     * @var string[]
     */
    private array $hiddenFilterNames;

    /**
     * @var string[]
     */
    private array $hiddenPaths;

    /**
     * @var string[]
     */
    private array $hiddenPathsWithoutTranslations;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $hiddenFields
     */
    public function __construct(array $hiddenFields)
    {
        $this->hiddenFilterNames = array_map(static fn (FieldDescriptor $field): string => $field->getFilterName(), $hiddenFields);
        $this->hiddenPaths = array_merge(...array_map(static fn (FieldDescriptor $field): array => $field->getProperties(), array_values($hiddenFields)));
        $this->hiddenPathsWithoutTranslations = array_map(self::removeTranslationsSegmentsBeforeLast(...), $this->hiddenPaths);
    }

    public function hides(FilterInterface $filter): bool
    {
        return in_array($filter->getName(), $this->hiddenFilterNames, true)
            || self::overlapsAnyPath($filter->getPath(), $this->hiddenPaths)
            || self::overlapsAnyPath(self::removeAllTranslationsSegments($filter->getPath()), $this->hiddenPathsWithoutTranslations);
    }

    /**
     * Whether the path is one of the paths, leads under one of them or lies above one of them, by whole segments.
     * An empty path is the listed entity itself, which lies above every path.
     *
     * @param string[] $paths
     */
    private static function overlapsAnyPath(string $path, array $paths): bool
    {
        foreach ($paths as $otherPath) {
            if ($path === '' || $path === $otherPath || str_starts_with($path, $otherPath . '.') || str_starts_with($otherPath, $path . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizes a hidden path. A `translations` segment followed by a field may read the same column as the field
     * alone (see `PathWalker`) — `status.translations.name` becomes `status.name`. A last segment `translations` is
     * the association itself and stays, so the hidden association is never reduced to its owner.
     */
    private static function removeTranslationsSegmentsBeforeLast(string $path): string
    {
        $segments = explode('.', $path);
        $lastSegment = array_pop($segments);

        return implode('.', [...self::removeTranslations($segments), $lastSegment]);
    }

    /**
     * Normalizes the path of a filter. A last segment `translations` is removed as well, so `status.translations`
     * becomes `status` and lies above every hidden translated field of the status.
     */
    private static function removeAllTranslationsSegments(string $path): string
    {
        return implode('.', self::removeTranslations(explode('.', $path)));
    }

    /**
     * @param string[] $segments
     * @return string[]
     */
    private static function removeTranslations(array $segments): array
    {
        return array_filter($segments, static fn (string $segment): bool => $segment !== PathDescription::TRANSLATIONS_SEGMENT);
    }
}
