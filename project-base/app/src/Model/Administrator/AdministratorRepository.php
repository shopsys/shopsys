<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository as BaseAdministratorRepository;

/**
 * @method \App\Model\Administrator\Administrator|null findById(int $administratorId)
 * @method \App\Model\Administrator\Administrator getById(int $administratorId)
 * @method \App\Model\Administrator\Administrator|null findByUserName(string $administratorUserName)
 * @method \App\Model\Administrator\Administrator getByUserName(string $administratorUserName)
 * @method \App\Model\Administrator\Administrator|null findByUuid(string $uuid)
 * @method \App\Model\Administrator\Administrator getByEmail(string $administratorEmail)
 * @method \App\Model\Administrator\Administrator|null findByUserNameWithPasswordFilled(string $administratorUserName)
 */
class AdministratorRepository extends BaseAdministratorRepository
{
}
