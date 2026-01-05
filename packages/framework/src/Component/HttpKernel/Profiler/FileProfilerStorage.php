<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpKernel\Profiler;

use GuzzleHttp\Exception\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector;
use Symfony\Component\HttpKernel\Profiler\FileProfilerStorage as BaseFileProfilerStorage;
use Symfony\Component\HttpKernel\Profiler\Profile;
use function dirname;
use function function_exists;
use function GuzzleHttp\json_decode;
use const LOCK_EX;

class FileProfilerStorage extends BaseFileProfilerStorage
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function write(Profile $profile): bool
    {
        $file = $this->getFilename($profile->getToken());

        $profileIndexed = is_file($file);

        if (!$profileIndexed) {
            // Create directory
            $dir = dirname($file);

            if (!is_dir($dir) && @mkdir($dir, 0777, true) === false && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Unable to create the storage directory (%s).', $dir));
            }
        }

        $data = $this->getDataByProfile($profile);

        if (file_put_contents($file, $data, LOCK_EX) === false) {
            return false;
        }

        if (!$profileIndexed) {
            $file = fopen($this->getIndexFilename(), 'a');

            // Add to index
            if ($file === false) {
                return false;
            }

            fputcsv($file, [
                $profile->getToken(),
                $profile->getIp(),
                $profile->getMethod(),
                $profile->getUrl(),
                $profile->getTime(),
                $profile->getParentToken(),
                $profile->getStatusCode(),
            ]);
            fclose($file);
        }

        return true;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
     */
    protected function updateProfileUrlForGqlRequests(Profile $profile): void
    {
        if (str_ends_with($profile->getUrl(), '/graphql/') && $profile->hasCollector('request')) {
            $collector = $profile->getCollector('request');

            if ($collector instanceof RequestDataCollector) {
                try {
                    $content = json_decode($collector->getContent(), true);

                    if (is_array($content) && array_key_exists('query', $content) && strpos($content['query'], '{')) {
                        $queryString = $content['query'];
                        $re = '/(?<type>query|mutation)[^{]*{\s*(?<name>[a-zA-Z0-9]+)/m';
                        $matches = [];

                        if (preg_match($re, $queryString, $matches) !== false) {
                            $profile->setUrl($profile->getUrl() . ' - ' . $matches['type'] . ' ' . $matches['name']);
                        }
                    }
                } catch (InvalidArgumentException) {
                    return;
                }
            }
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Profiler\Profile $profile
     * @return string
     */
    protected function getDataByProfile(Profile $profile): string
    {
        $profileToken = $profile->getToken();
        // when there are errors in sub-requests, the parent and/or children tokens
        // may equal the profile token, resulting in infinite loops
        $parentToken = $profile->getParentToken() !== $profileToken ? $profile->getParentToken() : null;
        $childrenToken = array_filter(array_map(function (Profile $p) use ($profileToken) {
            return $profileToken !== $p->getToken() ? $p->getToken() : null;
        }, $profile->getChildren()));

        $this->updateProfileUrlForGqlRequests($profile);

        // Store profile
        $data = [
            'token' => $profileToken,
            'parent' => $parentToken,
            'children' => $childrenToken,
            'data' => $profile->getCollectors(),
            'ip' => $profile->getIp(),
            'method' => $profile->getMethod(),
            'url' => $profile->getUrl(),
            'time' => $profile->getTime(),
            'status_code' => $profile->getStatusCode(),
        ];

        $data = serialize($data);

        if (function_exists('gzencode')) {
            $data = gzencode($data, 3);
        }

        return $data;
    }
}
