<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

use Override;
use Shopsys\FrameworkBundle\Component\Translation\Exception\InstanceNotInjectedException;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Translator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    public const string DEFAULT_TRANSLATION_DOMAIN = 'messages';
    public const string DATA_FIXTURES_TRANSLATION_DOMAIN = 'dataFixtures';
    public const string VALIDATOR_TRANSLATION_DOMAIN = 'validators';
    public const string TESTS_TRANSLATION_DOMAIN = 'tests';
    public const string CUSTOMER_TRANSLATION_DOMAIN = 'customerMessages';
    public const string CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN = 'customerValidators';
    public const string SOURCE_LOCALE = 'en';

    protected static ?self $self;

    public function __construct(
        protected readonly TranslatorInterface|LocaleAwareInterface|DataCollectorTranslator $originalTranslator,
        protected readonly TranslatorBagInterface|DataCollectorTranslator $originalTranslatorBag,
        protected readonly TranslatorInterface|LocaleAwareInterface|IdentityTranslator $identityTranslator,
        protected readonly MessageIdNormalizer $messageIdNormalizer,
    ) {
    }

    /**
     * Passes trans() call to original translator for logging purposes.
     *
     * @param array<string, mixed> $parameters
     */
    #[Override]
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
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

    protected function resolveLocale(?string $locale): ?string
    {
        return $locale ?? $this->getLocale();
    }

    protected function resolveDomain(?string $domain): string
    {
        return $domain ?? static::DEFAULT_TRANSLATION_DOMAIN;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLocale(): string
    {
        return $this->originalTranslator->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLocale(string $locale): void
    {
        $this->originalTranslator->setLocale($locale);
        $this->identityTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getCatalogue($locale = null): MessageCatalogueInterface
    {
        return $this->originalTranslatorBag->getCatalogue($locale);
    }

    public static function injectSelf(self $translator): void
    {
        self::$self = $translator;
    }

    /**
     * @param array<string, mixed> $parameters
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
    #[Override]
    public function getCatalogues(): array
    {
        if ($this->originalTranslator instanceof DataCollectorTranslator) {
            return $this->originalTranslator->getCatalogues();
        }

        return [];
    }
}
