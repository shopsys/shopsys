<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator as BaseAdministrator;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="administrators",
 *   indexes={
 *     @ORM\Index(columns={"username"})
 *   }
 * )
 * @method __construct(\App\Model\Administrator\AdministratorData $administratorData)
 * @method edit(\App\Model\Administrator\AdministratorData $administratorData)
 * @method setData(\App\Model\Administrator\AdministratorData $administratorData)
 */
class Administrator extends BaseAdministrator
{
}
