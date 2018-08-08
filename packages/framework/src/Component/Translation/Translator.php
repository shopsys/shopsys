<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Translator implements TranslatorInterface, TranslatorBagInterface
{
    const DEFAULT_DOMAIN = 'messages';
    const SOURCE_LOCALE = 'en';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Translation\Translator|null
     */
    private static $self;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $originalTranslator;

    /**
     * @var \Symfony\Component\Translation\TranslatorBagInterface
     */
    private $originalTranslatorBag;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $identityTranslator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Translation\MessageIdNormalizer
     */
    private $messageIdNormalizer;

    public function __construct(
        TranslatorInterface $originalTranslator,
        TranslatorBagInterface $originalTranslatorBag,
        TranslatorInterface $identityTranslator,
        MessageIdNormalizer $messageIdNormalizer
    ) {
        $this->originalTranslator = $originalTranslator;
        $this->originalTranslatorBag = $originalTranslatorBag;
        $this->identityTranslator = $identityTranslator;
        $this->messageIdNormalizer = $messageIdNormalizer;
    }

    /**
     * Passes trans() call to original translator for logging purposes.
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $normalizedId = $this->messageIdNormalizer->normalizeMessageId($id);
        $resolvedLocale = $this->resolveLocale($locale);
        $resolvedDomain = $this->resolveDomain($domain);

        $catalogue = $this->originalTranslatorBag->getCatalogue($resolvedLocale);

        if ($resolvedLocale === self::SOURCE_LOCALE) {
            if ($catalogue->defines($normalizedId, $resolvedDomain)) {
                $message = $this->originalTranslator->trans($normalizedId, $parameters, $resolvedDomain, $resolvedLocale);
            } else {
                $message = $this->identityTranslator->trans($normalizedId, $parameters, $resolvedDomain, $resolvedLocale);
            }
        } else {
            $message = $this->originalTranslator->trans($normalizedId, $parameters, $resolvedDomain, $resolvedLocale);
        }

        return $message;
    }

    /**
     * Passes transChoice() call to original translator for logging purposes.
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $normalizedId = $this->messageIdNormalizer->normalizeMessageId($id);
        $resolvedLocale = $this->resolveLocale($locale);
        $resolvedDomain = $this->resolveDomain($domain);

        $catalogue = $this->originalTranslatorBag->getCatalogue($resolvedLocale);

        if ($resolvedLocale === self::SOURCE_LOCALE) {
            if ($catalogue->defines($normalizedId, $resolvedDomain)) {
                $message = $this->originalTranslator->transChoice($normalizedId, $number, $parameters, $resolvedDomain, $resolvedLocale);
            } else {
                $message = $this->identityTranslator->transChoice($normalizedId, $number, $parameters, $resolvedDomain, $resolvedLocale);
            }
        } else {
            $message = $this->originalTranslator->transChoice($normalizedId, $number, $parameters, $resolvedDomain, $resolvedLocale);
        }

        return $message;
    }

    /**
     * @param string|null $locale
     */
    private function resolveLocale(?string $locale): ?string
    {
        if ($locale === null) {
            return $this->getLocale();
        }

        return $locale;
    }

    /**
     * @param string|null $domain
     */
    private function resolveDomain(?string $domain): string
    {
        if ($domain === null) {
            return self::DEFAULT_DOMAIN;
        }

        return $domain;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocale()
    {
        return $this->originalTranslator->getLocale();
    }

    /**
     * {@inheritDoc}
     */
    public function setLocale($locale)
    {
        $this->originalTranslator->setLocale($locale);
        $this->identityTranslator->setLocale($locale);
    }

    /**
     * {@inheritDoc}
     */
    public function getCatalogue($locale = null)
    {
        return $this->originalTranslatorBag->getCatalogue($locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Translation\Translator $translator
     */
    public static function injectSelf(self $translator): void
    {
        self::$self = $translator;
    }

    /**
     * @param string|null $domain
     * @param string|null $locale
     */
    public static function staticTrans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        if (self::$self === null) {
            throw new \Shopsys\FrameworkBundle\Component\Translation\Exception\InstanceNotInjectedException();
        }

        return self::$self->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param string|null $domain
     * @param string|null $locale
     */
    public static function staticTransChoice(string $id, int $number, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        if (self::$self === null) {
            throw new \Shopsys\FrameworkBundle\Component\Translation\Exception\InstanceNotInjectedException();
        }

        return self::$self->transChoice($id, $number, $parameters, $domain, $locale);
    }
}
