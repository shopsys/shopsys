<?php

declare(strict_types=1);

namespace App\Model\Transfer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="tranfers")
 * @ORM\Entity
 */
class Transfer
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false, unique=true)
     */
    protected $identifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     * @param string $identifier
     * @param string $name
     */
    public function __construct(string $identifier, string $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
