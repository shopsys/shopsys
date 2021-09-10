<?php

declare(strict_types=1);

namespace Tests\App\Test\Elasticsearch;

use Elasticsearch\Client;

class TestClient extends Client
{
    /**
     * @param array $params
     * @return array
     */
    public function search(array $params = [])
    {
        /*
         * connection will be closed immediately after request to prevent stale connections while testing
         * see https://github.com/elastic/elasticsearch-php/issues/225
         */
        $params['client'] = [
            'curl' => [
                CURLOPT_FORBID_REUSE => true,
            ],
        ];

        return parent::search($params);
    }
}
