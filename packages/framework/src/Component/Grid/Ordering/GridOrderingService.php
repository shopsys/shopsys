<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

class GridOrderingService
{
    public function setPosition(?\Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface $entity, int $position): void
    {
        if ($entity instanceof OrderableEntityInterface) {
            $entity->setPosition($position);
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException();
        }
    }
}
