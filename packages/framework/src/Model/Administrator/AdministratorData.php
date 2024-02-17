<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\AdminBundle\Component\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\Validator\Constraints as Assert;

class AdministratorData implements AdminIdentifierInterface
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    #[Assert\NotBlank(message: "Please enter username")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Username cannot be longer than {{ limit }} characters"
    )]
    public $username;

    /**
     * @var string|null
     */
    #[Assert\NotBlank(message: "Please enter full name")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Full name cannot be longer than {{ limit }} characters"
    )]
    public $realName;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var string|null
     */
    #[Assert\Email(
        message: "Please enter valid email"
    )]
    #[Assert\NotBlank(message: "Please enter email")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Email cannot be longer than {{ limit }} characters"
    )]
    public $email;

    /**
     * @var string[]
     */
    public $roles;

    public function __construct()
    {
        $this->roles[] = Roles::ROLE_ADMIN;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
