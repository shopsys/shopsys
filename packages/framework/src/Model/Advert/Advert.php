<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="adverts")
 * @ORM\Entity
 */
class Advert
{
    const TYPE_IMAGE = 'image';
    const TYPE_CODE = 'code';

    const POSITION_HEADER = 'header';
    const POSITION_FOOTER = 'footer';
    const POSITION_PRODUCT_LIST = 'productList';
    const POSITION_LEFT_SIDEBAR = 'leftSidebar';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $type;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $link;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $positionName;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden;

    public function __construct(AdvertData $advert)
    {
        $this->domainId = $advert->domainId;
        $this->name = $advert->name;
        $this->type = $advert->type;
        $this->code = $advert->code;
        $this->link = $advert->link;
        $this->positionName = $advert->positionName;
        $this->hidden = $advert->hidden;
    }

    public function edit(AdvertData $advert): void
    {
        $this->domainId = $advert->domainId;
        $this->name = $advert->name;
        $this->type = $advert->type;
        $this->code = $advert->code;
        $this->link = $advert->link;
        $this->positionName = $advert->positionName;
        $this->hidden = $advert->hidden;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getPositionName(): ?string
    {
        return $this->positionName;
    }
}
