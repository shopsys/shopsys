<?php

namespace Shopsys\FrameworkBundle\Component\Grid\InlineEdit;

class GridInlineEditRegistry
{

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface[]
     */
    private $gridInlineEdits;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface[] $gridInlineEdits
     */
    public function __construct(iterable $gridInlineEdits)
    {
        $this->gridInlineEdits = $gridInlineEdits;
    }

    public function getGridInlineEdit(string $serviceName): \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\GridInlineEditInterface
    {
        foreach ($this->gridInlineEdits as $gridInlineEdit) {
            if ($gridInlineEdit instanceof $serviceName && $gridInlineEdit instanceof GridInlineEditInterface) {
                return $gridInlineEdit;
            }
        }

        throw new \Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidServiceException($serviceName);
    }
}
