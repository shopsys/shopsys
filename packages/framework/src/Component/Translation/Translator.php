<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use Shopsys\FrameworkBundle\Component\Translation\Exception\InstanceNotInjectedException;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Translator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    public const DEFAULT_TRANSLATION_DOMAIN = 'messages';
    public const DATA_FIXTURES_TRANSLATION_DOMAIN = 'dataFixtures';
    public const VALIDATOR_TRANSLATION_DOMAIN = 'validators';
    public const TESTS_TRANSLATION_DOMAIN = 'tests';
    public const SOURCE_LOCALE = 'en';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Translation\Translator|null
     */
    protected static ?self $self;

    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface|\Symfony\Contracts\Translation\LocaleAwareInterface|\Symfony\Component\Translation\DataCollectorTranslator $originalTranslator
     * @param \Symfony\Component\Translation\TranslatorBagInterface|\Symfony\Component\Translation\DataCollectorTranslator $originalTranslatorBag
     * @param \Symfony\Contracts\Translation\TranslatorInterface|\Symfony\Contracts\Translation\LocaleAwareInterface|\Symfony\Component\Translation\IdentityTranslator $identityTranslator
     * @param \Shopsys\FrameworkBundle\Component\Translation\MessageIdNormalizer $messageIdNormalizer
     */
    public function __construct(
        protected readonly TranslatorInterface|LocaleAwareInterface|DataCollectorTranslator $originalTranslator,
        protected readonly TranslatorBagInterface|DataCollectorTranslator $originalTranslatorBag,
        protected readonly TranslatorInterface|LocaleAwareInterface|IdentityTranslator $identityTranslator,
        protected readonly MessageIdNormalizer $messageIdNormalizer,
    ) {
    }

    /**
     * Passes trans() call to original translator for logging purposes.
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        $normalizedId = $this->messageIdNormalizer->normalizeMessageId($id);
        $resolvedLocale = $this->resolveLocale($locale);
        $resolvedDomain = $this->resolveDomain($domain);

        $catalogue = $this->originalTranslatorBag->getCatalogue($resolvedLocale);

        if ($resolvedLocale === self::SOURCE_LOCALE) {
            if ($catalogue->defines($normalizedId, $resolvedDomain)) {
                $message = $this->originalTranslator->trans(
                    $normalizedId,
                    $parameters,
                    $resolvedDomain,
                    $resolvedLocale,
                );
            } else {
                $message = $this->identityTranslator->trans(
                    $normalizedId,
                    $parameters,
                    $resolvedDomain,
                    $resolvedLocale,
                );
            }
        } else {
            $message = $this->originalTranslator->trans($normalizedId, $parameters, $resolvedDomain, $resolvedLocale);
        }

        return $message;
    }

    /**
     * @param string|null $locale
     * @return string|null
     */
    protected function resolveLocale(?string $locale): ?string
    {
        return $locale ?? $this->getLocale();
    }

    /**
     * @param string|null $domain
     * @return string
     */
    protected function resolveDomain(?string $domain): string
    {
        return $domain ?? static::DEFAULT_TRANSLATION_DOMAIN;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->originalTranslator->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): void
    {
        $this->originalTranslator->setLocale($locale);
        $this->identityTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null): MessageCatalogueInterface
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
     * @param string $id
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public static function staticTrans(
        string $id,
        array $parameters = [],
        ?string $domain = null,
        ?string $locale = null,
    ): string {
        if (self::$self === null) {
            throw new InstanceNotInjectedException();
        }

        return self::$self->trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogues(): array
    {
        if ($this->originalTranslator instanceof DataCollectorTranslator) {
            return $this->originalTranslator->getCatalogues();
        }

        return [];
    }
}
