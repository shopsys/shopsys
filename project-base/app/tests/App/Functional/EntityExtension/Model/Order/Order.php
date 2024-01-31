<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model\Order;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 */
class Order
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $uuid;

    /**
     * @ORM\Column(type="string", length=30, unique=true, nullable=false)
     */
    protected string $number;

    /**
     * @ORM\Column(type="datetime")
     */
    protected DateTime $createdAt;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Tests\App\Functional\EntityExtension\Model\Order\OrderItem>
     * @ORM\OneToMany(
     *     targetEntity="Tests\App\Functional\EntityExtension\Model\Order\OrderItem",
     *     mappedBy="order",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    protected Collection $items;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $email;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $domainId;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\Order\OrderItem $item
     */
    public function addItem(OrderItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }
    }

    public function setMandatoryData(): void
    {
        $this->number = '00000001';
        $this->email = 'email@exmple.com';
        $this->domainId = 1;
    }
}
