<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator as BaseAdministrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorData as BaseAdministratorData;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="administrators",
 *   indexes={
 *     @ORM\Index(columns={"username"})
 *   }
 * )
 * @method setData(\App\Model\Administrator\AdministratorData $administratorData)
 */
class Administrator extends BaseAdministrator
{
    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    public function __construct(BaseAdministratorData $administratorData)
    {
        parent::__construct($administratorData);
    }

    /**
     * @param \App\Model\Administrator\AdministratorData $administratorData
     */
    public function edit(BaseAdministratorData $administratorData): void
    {
        parent::edit($administratorData);
    }
}
