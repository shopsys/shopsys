<?php

declare(strict_types=1);

namespace App\Component\Image\Kraken;

use Kraken;

class KrakenFactory
{
    /**
     * @var \App\Component\Image\Kraken\KrakenConfig
     */
    private $krakenConfig;

    /**
     * @param \App\Component\Image\Kraken\KrakenConfig $krakenConfig
     */
    public function __construct(KrakenConfig $krakenConfig)
    {
        $this->krakenConfig = $krakenConfig;
    }

    /**
     * @return \Kraken
     */
    public function createApi(): Kraken
    {
        return new Kraken(
            $this->krakenConfig->getApiKey(),
            $this->krakenConfig->getApiSecret()
        );
    }
}
