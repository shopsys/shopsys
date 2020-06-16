<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag as BaseFlag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagData;

/**
 * @property \App\Model\Product\Flag\FlagData $flagData
 *
 * @ORM\Table(name="flags")
 * @ORM\Entity
 * @method setTranslations(\App\Model\Product\Flag\FlagData $flagData)
 */
class Flag extends BaseFlag
{
    public const AKENEO_CODE_SALE = 'flag__product_sale';
    public const AKENEO_CODE_ACTION = 'flag__product_action';
    public const AKENEO_CODE_SCONTO = 'flag__product_sconto';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    protected $sale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $akeneoCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $noticeLowPrice;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $noticeHighPrice;

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    public function __construct(FlagData $flagData)
    {
        parent::__construct($flagData);

        $this->sale = $flagData->sale;
        $this->akeneoCode = $flagData->akeneoCode ?? '';
        $this->noticeLowPrice = $flagData->noticeLowPrice;
        $this->noticeHighPrice = $flagData->noticeHighPrice;
    }

    /**
     * @param \App\Model\Product\Flag\FlagData $flagData
     */
    public function edit(FlagData $flagData): void
    {
        parent::edit($flagData);

        $this->sale = $flagData->sale;
        $this->noticeLowPrice = $flagData->noticeLowPrice;
        $this->noticeHighPrice = $flagData->noticeHighPrice;
    }

    /**
     * @return bool
     */
    public function isSale(): bool
    {
        return $this->sale;
    }

    /**
     * @return string
     */
    public function getAkeneoCode(): ?string
    {
        return $this->akeneoCode;
    }

    /**
     * @return null|string
     */
    public function getNoticeLowPrice(): ?string
    {
        return $this->noticeLowPrice;
    }

    /**
     * @return null|string
     */
    public function getNoticeHighPrice(): ?string
    {
        return $this->noticeHighPrice;
    }
}
