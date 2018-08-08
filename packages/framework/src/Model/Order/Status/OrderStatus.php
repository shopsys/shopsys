<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="order_statuses")
 * @ORM\Entity
 */
class OrderStatus extends AbstractTranslatableEntity
{
    const TYPE_NEW = 1;
    const TYPE_IN_PROGRESS = 2;
    const TYPE_DONE = 3;
    const TYPE_CANCELED = 4;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation[]
     *
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation")
     */
    protected $translations;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     * @param int $type
     */
    public function __construct(OrderStatusData $orderStatusData, $type)
    {
        $this->translations = new ArrayCollection();
        $this->setType($type);
        $this->setTranslations($orderStatusData);
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    protected function setTranslations(OrderStatusData $orderStatusData)
    {
        foreach ($orderStatusData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation
    {
        return new OrderStatusTranslation();
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    protected function setType($type)
    {
        if (in_array($type, [
            self::TYPE_NEW,
            self::TYPE_IN_PROGRESS,
            self::TYPE_DONE,
            self::TYPE_CANCELED,
        ], true)) {
            $this->type = $type;
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Order\Status\Exception\InvalidOrderStatusTypeException($type);
        }
    }

    public function edit(OrderStatusData $orderStatusData)
    {
        $this->setTranslations($orderStatusData);
    }
}
