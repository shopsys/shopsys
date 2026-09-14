<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;

/**
 * Records every request a datagrid sends, so that a test asserts on what the datagrid asked for. Describes
 * every path as a single text value unless told otherwise.
 */
final class CapturingAdapter implements PathDescribingAdapterInterface
{
    /**
     * @var \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest[]
     */
    public array $requests = [];

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions
     * @param bool $describesUnknownPaths False refuses any path not described, the way a real schema does
     */
    public function __construct(
        private readonly DataSourceInterface $dataSource,
        private readonly array $pathDescriptions = [],
        private readonly bool $describesUnknownPaths = true,
    ) {
    }

    /**
     * The in-memory builder answers for its medium as a whole, the same way it does in the array adapter.
     */
    #[Override]
    public function getExpressionCapabilities(): PhpExpressionBuilder
    {
        return new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function describePath(string $path): PathDescription
    {
        if (array_key_exists($path, $this->pathDescriptions)) {
            return $this->pathDescriptions[$path];
        }

        if ($this->describesUnknownPaths === false) {
            throw new PathNotFoundException($path, $path, self::class);
        }

        return new PathDescription($path, PathCardinalityEnum::TO_ONE, PathValueTypeEnum::STRING);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDatasource(DatasourceRequest $request): DataSourceInterface
    {
        $this->requests[] = $request;

        return $this->dataSource;
    }

    public function getLastRequest(): DatasourceRequest
    {
        return $this->requests[array_key_last($this->requests)];
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor>
     */
    public function getLastRequestFields(): array
    {
        $fields = [];

        foreach ($this->getLastRequest()->fields as $field) {
            $fields[$field->getName()] = $field;
        }

        return $fields;
    }
}
