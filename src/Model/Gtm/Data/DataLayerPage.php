<?php

declare(strict_types = 1);

namespace App\Model\Gtm\Data;

use App\Model\Gtm\Exception\GtmException;
use JsonSerializable;

class DataLayerPage implements JsonSerializable
{
    public const TYPE_ARTICLE = 'text';
    public const TYPE_BLOG = 'blog';
    public const TYPE_BLOG_ARTICLE = 'article';
    public const TYPE_CART = 'cart';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_CONTACT = 'contact'; // TODO tato stránka zatím neexistuje
    public const TYPE_HOME = 'home';
    public const TYPE_ORDER_STEP1 = 'order step 1';
    public const TYPE_ORDER_STEP2 = 'order step 2';
    public const TYPE_OTHER = 'other';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_PURCHASE_FAIL = 'purchase fail';
    public const TYPE_SEARCH = 'search';
    public const TYPE_STORES = 'stores'; // TODO tato stránka zatím neexistuje
    public const TYPE_ERROR = 'error';
    public const TYPE_ERROR_404 = '404';

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var string[]|null
     */
    protected $category;

    /**
     * @var int[]|null
     */
    protected $categoryIds;

    /**
     * @var string|null
     */
    protected $categoryLevel;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        if (!in_array($type, [
            self::TYPE_ARTICLE,
            self::TYPE_BLOG,
            self::TYPE_BLOG_ARTICLE,
            self::TYPE_CART,
            self::TYPE_CATEGORY,
            self::TYPE_CONTACT,
            self::TYPE_HOME,
            self::TYPE_ORDER_STEP1,
            self::TYPE_ORDER_STEP2,
            self::TYPE_OTHER,
            self::TYPE_PRODUCT,
            self::TYPE_PURCHASE,
            self::TYPE_PURCHASE_FAIL,
            self::TYPE_SEARCH,
            self::TYPE_STORES,
            self::TYPE_ERROR,
            self::TYPE_ERROR_404,
        ], true)) {
            throw new GtmException(sprintf('Invalid argument $type "%s"', $type));
        }

        $this->type = $type;
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategoryIds(array $categoryIds): void
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @param string[] $category
     */
    public function setCategory(array $category): void
    {
        $this->category = $category;
    }

    /**
     * @param string $categoryLevel
     */
    public function setCategoryLevel(string $categoryLevel): void
    {
        $this->categoryLevel = $categoryLevel;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return array_filter(get_object_vars($this));
    }
}
