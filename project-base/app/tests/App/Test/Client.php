<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Nette\Utils\Json;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class Client extends KernelBrowser
{
    private const DEFAULT_HEADERS = [
        'CONTENT_TYPE' => 'application/json',
    ];

    /**
     * @param string $method
     * @param string $uri
     * @param mixed[] $parameters
     * @param mixed[] $files
     * @param mixed[] $server
     * @param string|null $content
     * @param bool $changeHistory
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        ?string $content = null,
        bool $changeHistory = true,
    ): Crawler {
        return parent::request(
            $method,
            $uri,
            $parameters,
            $files,
            array_merge(self::DEFAULT_HEADERS, $server),
            $content,
            $changeHistory,
        );
    }

    /**
     * @param string $uri
     * @param mixed[] $headers
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function get(string $uri, array $headers = []): Crawler
    {
        return $this->request('GET', $uri, [], [], $headers);
    }

    /**
     * @param string $uri
     * @param mixed[] $body
     * @param mixed[] $headers
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function post(string $uri, array $body, array $headers = []): Crawler
    {
        return $this->request('POST', $uri, [], [], $headers, Json::encode($body));
    }

    /**
     * @param string $uri
     * @param mixed[] $body
     * @param mixed[] $headers
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function put(string $uri, array $body, array $headers = []): Crawler
    {
        return $this->request('PUT', $uri, [], [], $headers, Json::encode($body));
    }

    /**
     * @param string $uri
     * @param mixed[] $headers
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function delete(string $uri, array $headers = []): Crawler
    {
        return $this->request('DELETE', $uri, [], [], $headers);
    }

    /**
     * @return mixed[]
     */
    public function getResponseData(): array
    {
        return Json::decode((string)$this->getResponse()->getContent(), Json::FORCE_ARRAY);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->getResponse()->getStatusCode();
    }
}
