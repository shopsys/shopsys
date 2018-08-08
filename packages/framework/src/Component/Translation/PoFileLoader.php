<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\PoFileLoader as BasePoFileLoader;
use Symfony\Component\Translation\MessageCatalogue;

class PoFileLoader extends BasePoFileLoader
{
    /**
     * @param string $resource
     * @param string $locale
     * @param string $domain
     */
    public function load($resource, $locale, $domain = 'messages'): \Symfony\Component\Translation\MessageCatalogue
    {
        $catalogue = $this->loadIncludingEmpty($resource, $locale, $domain);

        $messages = $catalogue->all($domain);

        $filteredMessages = [];
        foreach ($messages as $key => $message) {
            if ($message !== '') {
                $filteredMessages[$key] = $message;
            }
        }

        $filteredCatalogue = new MessageCatalogue($locale);
        $filteredCatalogue->add($filteredMessages, $domain);
        $filteredCatalogue->addResource(new FileResource($resource));

        return $filteredCatalogue;
    }

    public function loadIncludingEmpty(string $resource, string $locale, string $domain = 'messages'): \Symfony\Component\Translation\MessageCatalogue
    {
        return parent::load($resource, $locale, $domain);
    }
}
