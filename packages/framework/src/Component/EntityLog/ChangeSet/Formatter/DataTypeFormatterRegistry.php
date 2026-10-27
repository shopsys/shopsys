<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Shopsys\FrameworkBundle\Component\EntityLog\Exception\DataTypeFormatterNotFoundException;
use Webmozart\Assert\Assert;

class DataTypeFormatterRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\DataTypeFormatterInterface[]
     */
    protected array $dataTypeFormatters;

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\DataTypeFormatterInterface> $dataTypeFormatters
     */
    public function __construct(iterable $dataTypeFormatters)
    {
        $dataTypeFormattersArray = [...$dataTypeFormatters];
        Assert::allIsInstanceOf($dataTypeFormattersArray, DataTypeFormatterInterface::class);

        usort(
            $dataTypeFormattersArray,
            static fn (DataTypeFormatterInterface $a, DataTypeFormatterInterface $b) => $b->getPriority() <=> $a->getPriority(),
        );

        $this->dataTypeFormatters = $dataTypeFormattersArray;
    }

    public function getDataTypeFormatter(string $dataType): DataTypeFormatterInterface
    {
        foreach ($this->dataTypeFormatters as $dataTypeFormatter) {
            if ($dataTypeFormatter->supports($dataType)) {
                return $dataTypeFormatter;
            }
        }

        throw new DataTypeFormatterNotFoundException(sprintf('No entity log formatter supports the data type "%s".', $dataType));
    }
}
