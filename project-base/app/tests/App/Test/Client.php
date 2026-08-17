<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Nette\Utils\Json;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class Client extends KernelBrowser
{
    private const DEFAULT_HEADERS = [
        'CONTENT_TYPE' => 'application/json',
    ];

    /**
     * @param mixed[] $parameters
     * @param mixed[] $files
     * @param mixed[] $server
     */
    #[Override]
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
     * @param mixed[] $headers
     */
    public function get(string $uri, array $headers = []): Crawler
    {
        return $this->request('GET', $uri, [], [], $headers);
    }

    /**
     * @param mixed[] $body
     * @param mixed[] $headers
     */
    public function post(string $uri, array $body, array $headers = []): Crawler
    {
        return $this->request('POST', $uri, [], [], $headers, Json::encode($body));
    }

    /**
     * @return mixed[]
     */
    public function getResponseData(): array
    {
        return Json::decode((string)$this->getResponse()->getContent(), true);
    }
}
