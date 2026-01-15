<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator as BaseAdministrator;

/**
 * @method __construct(\App\Model\Administrator\AdministratorData $administratorData)
 * @method edit(\App\Model\Administrator\AdministratorData $administratorData)
 * @method setData(\App\Model\Administrator\AdministratorData $administratorData)
 */
#[ORM\Table(name: 'administrators')]
#[ORM\Index(columns: ['username'])]
#[ORM\Entity]
class Administrator extends BaseAdministrator
{
}
