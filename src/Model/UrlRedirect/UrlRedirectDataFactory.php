<?php

declare(strict_types=1);

namespace App\Model\UrlRedirect;

class UrlRedirectDataFactory
{
    /**
     * @return \App\Model\UrlRedirect\UrlRedirectData
     */
    protected function createInstance(): UrlRedirectData
    {
        return new UrlRedirectData();
    }

    /**
     * @return \App\Model\UrlRedirect\UrlRedirectData
     */
    public function create(): UrlRedirectData
    {
        return $this->createInstance();
    }
}
