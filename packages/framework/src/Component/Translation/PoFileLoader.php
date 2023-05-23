<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Loader\PoFileLoader as BasePoFileLoader;
use Symfony\Component\Translation\MessageCatalogue;

class PoFileLoader extends BasePoFileLoader
{
    /**
     * @param \Symfony\Component\Finder\SplFileInfo|string $resource
     * @param string $locale
     * @param string $domain
     * @return \Symfony\Component\Translation\MessageCatalogue
     */
    public function load(
        mixed $resource,
        string $locale,
        string $domain = Translator::DEFAULT_TRANSLATION_DOMAIN,
    ): MessageCatalogue {
        if ($resource instanceof SplFileInfo) {
            $resource = $resource->getPathname();
        }

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

    /**
     * @param string $resource
     * @param string $locale
     * @param string $domain
     * @return \Symfony\Component\Translation\MessageCatalogue
     */
    public function loadIncludingEmpty(
        string $resource,
        string $locale,
        string $domain = Translator::DEFAULT_TRANSLATION_DOMAIN,
    ): MessageCatalogue {
        return parent::load($resource, $locale, $domain);
    }
}
