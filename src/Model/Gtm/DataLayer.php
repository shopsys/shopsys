<?php

declare(strict_types = 1);

namespace App\Model\Gtm;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DataLayer
{
    private const SESSION_DATA_KEY = 'gtm_data';

    private const SESSION_PUSHES_KEY = 'gtm_pushes';

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
     * @param string $eventName
     * @param array $eventData
     */
    public function addEvent(string $eventName, array $eventData = []): void
    {
        $data = $this->session->get(self::SESSION_DATA_KEY);

        $event = array_merge(
            ['event' => $eventName],
            $eventData
        );

        if (array_key_exists('event', $data)) {
            $this->push($event);
            return;
        }

        $data = array_merge(
            $data,
            $event
        );

        $this->session->set(self::SESSION_DATA_KEY, $data);
    }

    /**
     * @param array $data
     */
    public function push(array $data): void
    {
        $pushes = $this->session->get(self::SESSION_PUSHES_KEY);

        $pushes[] = $data;

        $this->session->set(self::SESSION_PUSHES_KEY, $pushes);
    }
}
