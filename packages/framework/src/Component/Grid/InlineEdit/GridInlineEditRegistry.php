<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidServiceException;

class GridInlineEditRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface[]
     */
    protected array $gridInlineEdits;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface[] $gridInlineEdits
     */
    public function __construct(iterable $gridInlineEdits)
    {
        $this->gridInlineEdits = $gridInlineEdits;
    }

    /**
     * @param string $serviceName
     * @return \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface
     */
    public function getGridInlineEdit($serviceName)
    {
        foreach ($this->gridInlineEdits as $gridInlineEdit) {
            if ($gridInlineEdit instanceof $serviceName && $gridInlineEdit instanceof GridInlineEditInterface) {
                return $gridInlineEdit;
            }
        }

        throw new InvalidServiceException($serviceName);
    }
}
