<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusDeletionForbiddenException;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation translation(?string $locale = null)
 * @method \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation> getTranslations()
 */
#[AsMcpTable]
#[ORM\Table(name: 'order_statuses')]
#[ORM\Entity]
class OrderStatus extends AbstractTranslatableEntity
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Override]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    protected $code;

    /**
     * @var \Doctrine\Common\Collections\Collection<string, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation>
     */
    #[Prezent\Translations(targetEntity: OrderStatusTranslation::class)]
    #[Override]
    protected $translations;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 25)]
    protected $type;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $productReviewsAllowed;

    /**
     * @param string $type
     * @param string $code
     */
    public function __construct(OrderStatusData $orderStatusData, $type, $code)
    {
        $this->translations = new ArrayCollection();
        $this->type = $type;
        $this->code = $code;
        $this->setData($orderStatusData);
    }

    public function edit(OrderStatusData $orderStatusData): void
    {
        $this->setData($orderStatusData);
    }

    protected function setData(OrderStatusData $orderStatusData): void
    {
        $this->productReviewsAllowed = $orderStatusData->productReviewsAllowed;

        $this->setTranslations($orderStatusData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    #[EntityLogIdentify(EntityLogIdentify::IS_LOCALIZED)]
    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    protected function setTranslations(OrderStatusData $orderStatusData): void
    {
        foreach ($orderStatusData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new OrderStatusTranslation();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function areProductReviewsAllowed()
    {
        return $this->productReviewsAllowed;
    }

    public function checkForDelete(): void
    {
        if ($this->type !== OrderStatusTypeEnum::TYPE_IN_PROGRESS) {
            throw new OrderStatusDeletionForbiddenException($this);
        }
    }
}
