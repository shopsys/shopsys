<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlSlugNormalizer;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class FriendlyUrlType extends AbstractType
{
    public const string SLUG_REGEX = '/^(?:[A-Za-z0-9_\-\/]|%[0-9A-Fa-f]{2})+$/';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(UrlListData::FIELD_SLUG, TextType::class, [
            'required' => true,
            'attr' => [
                'placeholder' => 'slug',
            ],
            'help' => t('If the slug contains non-ASCII characters (e.g. "café"), encode it as URL-encoded form (e.g. "caf%C3%A9"). You can use https://www.urlencoder.org/.'),
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Callback(callback: [$this, 'validateSlugEncoding']),
            ],
        ]);
    }

    public function validateSlugEncoding(mixed $slug, ExecutionContextInterface $context): void
    {
        if (!is_string($slug) || $slug === '') {
            return;
        }

        if (preg_match(static::SLUG_REGEX, $slug) === 1) {
            return;
        }

        $context->buildViolation(
            t('Slug containing non-ASCII characters must be URL-encoded. The encoded value of "%enteredValue%" is "%normalizedValue%".', [
                '%enteredValue%' => $slug,
                '%normalizedValue%' => FriendlyUrlSlugNormalizer::normalize($slug),
            ], Translator::VALIDATOR_TRANSLATION_DOMAIN),
        )
            ->addViolation();
    }
}
