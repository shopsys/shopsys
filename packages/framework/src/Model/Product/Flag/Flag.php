<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Attribute as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation translation(?string $locale = null)
 */
#[ORM\Table(name: 'flags')]
#[ORM\Entity]
class Flag extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    #[Prezent\Translations(targetEntity: FlagTranslation::class)]
    protected $translations;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 7)]
    protected $rgbColor;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $visible;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $lockedForDeletion;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
    #[ORM\JoinColumn(name: 'promotion_xy_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ProductPromotionXy::class)]
    protected $promotionXy;

    public function __construct(FlagData $flagData)
    {
        $this->uuid = $flagData->uuid ?: Uuid::uuid4()->toString();

        $this->translations = new ArrayCollection();
        $this->setData($flagData);
        $this->lockedForDeletion = false;
    }

    public function edit(FlagData $flagData)
    {
        $this->setData($flagData);
    }

    protected function setData(FlagData $flagData): void
    {
        $this->setTranslations($flagData);
        $this->rgbColor = $flagData->rgbColor;
        $this->visible = $flagData->visible;
        $this->promotionXy = $flagData->promotionXy;
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
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string[]
     */
    public function getNames()
    {
        $namesByLocale = [];

        foreach ($this->translations as $translation) {
            $namesByLocale[$translation->getLocale()] = $translation->getName();
        }

        return $namesByLocale;
    }

    /**
     * @return string
     */
    public function getRgbColor()
    {
        return $this->rgbColor;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return bool
     */
    public function hasPromotionXy()
    {
        return $this->promotionXy !== null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
    public function getPromotionXy()
    {
        return $this->promotionXy;
    }

    protected function setTranslations(FlagData $flagData)
    {
        foreach ($flagData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation
     */
    #[Override]
    protected function createTranslation()
    {
        return new FlagTranslation();
    }

    /**
     * @return bool
     */
    public function isLockedForDeletion()
    {
        return $this->lockedForDeletion;
    }
}
