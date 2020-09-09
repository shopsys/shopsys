<?php

declare(strict_types=1);

namespace App\Model\Gtm;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DataLayer
{
    private const SESSION_DATA_KEY = 'gtm_data';
    private const SESSION_PUSHES_KEY = 'gtm_pushes';

    public const EVENT_NAME_PRODUCT_IMPRESSIONS = 'ec.productImpressions';
    public const EVENT_NAME_PRODUCT_CLICK = 'ec.productClick';
    public const EVENT_NAME_PRODUCT_ADD_TO_CART = 'ec.addToCart';
    public const EVENT_NAME_PRODUCT_REMOVE_FROM_CART = 'ec.removeFromCart';
    public const EVENT_NAME_CHECKOUT_OPTION = 'ec.checkout_option';
    public const EVENT_NAME_PROMO_VIEW = 'ec.promoView';
    public const EVENT_NAME_PROMO_CLICK = 'ec.promoClick';
    public const EVENT_NAME_CATEGORY_FILTER = 'category.filter';

    public const LIST_NAME_HOME_TOP_PRODUCTS = 'home - akční zboží';
    public const LIST_NAME_HOME_SALE_PRODUCTS = 'home - výprodejové zboží';
    public const LIST_NAME_PRODUCT_SIMILAR_PRODUCTS = 'Product - podobné produkty';
    public const LIST_NAME_PRODUCT_ACCESSORIES = 'Product - příslušenství';
    public const LIST_NAME_PRODUCT_PROGRAM = 'Product - program';

    public const HOMEPAGE_SLIDER_LABEL = 'homepage-top-center';

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $pushes;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param string $locale
     */
    public function __construct(SessionInterface $session, string $locale)
    {
        $this->session = $session;

        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = $this->session->get(self::SESSION_DATA_KEY) ?? [];
        $this->session->remove(self::SESSION_DATA_KEY);

        return $data;
    }

    /**
     * @return array
     */
    public function getPushes(): array
    {
        $pushes = $this->session->get(self::SESSION_PUSHES_KEY) ?? [];
        $this->session->remove(self::SESSION_PUSHES_KEY);

        return $pushes;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $data = $this->session->get(self::SESSION_DATA_KEY);

        $value = $data[$key] ?? null;

        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $data = $this->session->get(self::SESSION_DATA_KEY);

        $data[$key] = $value;

        $this->session->set(self::SESSION_DATA_KEY, $data);
    }

    /**
     * @param array $eventData
     */
    public function addEvent(array $eventData): void
    {
        $data = $this->session->get(self::SESSION_DATA_KEY);

        $data = array_merge($data, $eventData);

        $this->session->set(self::SESSION_DATA_KEY, $data);
    }

    /**
     * @param string $eventName
     * @param array $pushData
     */
    public function push(string $eventName, array $pushData): void
    {
        $pushes = $this->session->get(self::SESSION_PUSHES_KEY);

        $data = array_merge(
            ['event' => $eventName],
            $pushData
        );

        $pushes[] = $data;

        $this->session->set(self::SESSION_PUSHES_KEY, $pushes);
    }
}
