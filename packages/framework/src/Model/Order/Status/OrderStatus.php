<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\InvalidOrderStatusTypeException;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusDeletionForbiddenException;

/**
 * @ORM\Table(name="order_statuses")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation translation(?string $locale = null)
 */
class OrderStatus extends AbstractTranslatableEntity
{
    public const TYPE_NEW = 1;
    public const TYPE_IN_PROGRESS = 2;
    public const TYPE_DONE = 3;
    public const TYPE_CANCELED = 4;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation[]|\Doctrine\Common\Collections\Collection
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     * @param int $type
     */
    public function __construct(OrderStatusData $orderStatusData, $type)
    {
        $this->translations = new ArrayCollection();
        $this->setType($type);
        $this->setData($orderStatusData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     */
    public function edit(OrderStatusData $orderStatusData): void
    {
        $this->setData($orderStatusData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     */
    protected function setData(OrderStatusData $orderStatusData): void
    {
        $this->setTranslations($orderStatusData);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null): string
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     */
    protected function setTranslations(OrderStatusData $orderStatusData): void
    {
        foreach ($orderStatusData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation
     */
    protected function createTranslation(): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation
    {
        return new OrderStatusTranslation();
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    protected function setType($type): void
    {
        if (!in_array($type, [
            self::TYPE_NEW,
            self::TYPE_IN_PROGRESS,
            self::TYPE_DONE,
            self::TYPE_CANCELED,
        ], true)) {
            throw new InvalidOrderStatusTypeException($type);
        }

        $this->type = $type;
    }

    public function checkForDelete(): void
    {
        if ($this->type !== self::TYPE_IN_PROGRESS) {
            throw new OrderStatusDeletionForbiddenException($this);
        }
    }
}
