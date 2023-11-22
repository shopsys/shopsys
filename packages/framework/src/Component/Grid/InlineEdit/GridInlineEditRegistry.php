<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidServiceException;

class GridInlineEditRegistry
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface[] $gridInlineEdits
     */
    public function __construct(protected iterable $gridInlineEdits)
    {
    }

    /**
     * @param string $serviceName
     * @return \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface
     */
    public function getGridInlineEdit($serviceName): \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface
    {
        foreach ($this->gridInlineEdits as $gridInlineEdit) {
            if ($gridInlineEdit instanceof $serviceName && $gridInlineEdit instanceof GridInlineEditInterface) {
                return $gridInlineEdit;
            }
        }

        throw new InvalidServiceException($serviceName);
    }
}
