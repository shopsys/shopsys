<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="flags")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation translation(?string $locale = null)
 */
class Flag extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation>
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=7)
     */
    protected $rgbColor;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $lockedForDeletion;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy")
     * @ORM\JoinColumn(name="promotion_xy_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $promotionXy;

    public function __construct(FlagData $flagData)
    {
        $this->uuid = $flagData->uuid ?: Uuid::uuid4()->toString();

        $this->translations = new ArrayCollection();
        $this->setData($flagData);
        $this->lockedForDeletion = false;
    }

    public function edit(FlagData $flagData): void
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

    protected function setTranslations(FlagData $flagData): void
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
