<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidServiceException;

/**
 * @template T of array<string, mixed>
 */
class GridInlineEditRegistry
{
    /**
     * @var array<\Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T>>
     */
    protected $gridInlineEdits;

    /**
     * @param array<\Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T>> $gridInlineEdits
     */
    public function __construct(iterable $gridInlineEdits)
    {
        $this->gridInlineEdits = $gridInlineEdits;
    }

    /**
     * @param string $serviceName
     * @return \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface<T>
     */
    public function getGridInlineEdit(string $serviceName): GridInlineEditInterface
    {
        foreach ($this->gridInlineEdits as $gridInlineEdit) {
            if ($gridInlineEdit instanceof $serviceName && $gridInlineEdit instanceof GridInlineEditInterface) {
                return $gridInlineEdit;
            }
        }

        throw new InvalidServiceException($serviceName);
    }
}
