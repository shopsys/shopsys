<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

interface OrderDetailSectionProviderInterface
{
    /**
     * @return iterable<\Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection>
     */
    public function getSections(): iterable;
}
