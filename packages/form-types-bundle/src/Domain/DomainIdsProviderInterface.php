<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle\Domain;

interface DomainIdsProviderInterface
{
    /**
     * @return int[]
     */
    public function getAllIds();

    /**
     * @return int[]
     */
    public function getAdminEnabledDomainIds(): array;
}
