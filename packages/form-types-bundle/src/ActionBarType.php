<?php

declare(strict_types=1);

namespace Shopsys\FormTypesBundle;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ActionBarType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'back_route',
                'back_url',
                'back_label',
                'save_label',
                'entity',
            ])
            ->setAllowedTypes('back_route', ['string', 'null'])
            ->setAllowedTypes('back_url', ['string', 'null'])
            ->setAllowedTypes('back_label', ['string'])
            ->setAllowedTypes('save_label', ['string', 'null'])
            ->setAllowedTypes('entity', ['object', 'null'])
            ->setDefaults([
                'back_route' => null,
                'back_url' => null,
                'back_label' => t('Back to overview'),
                'save_label' => null,
                'entity' => null,
                'mapped' => false,
            ]);

        $resolver->setNormalizer(
            'back_route',
            static function (Options $options, $value) {
                if ($value !== null && $options['back_url'] !== null) {
                    throw new LogicException('Cannot use the "back_route" and "back_url" options at the same time. Remove one of them.');
                }

                return $value;
            },
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['back_route'] !== null) {
            $backLink = $this->urlGenerator->generate($options['back_route']);
        } elseif ($options['back_url'] !== null) {
            $backLink = $options['back_url'];
        } else {
            $backLink = null;
        }

        if ($backLink !== null) {
            $builder->add('back_link', LinkType::class, [
                'link' => $backLink,
                'label' => $options['back_label'],
            ]);
        }

        $builder->add('save', SubmitType::class, [
            'label' => $options['save_label'] ?? ($options['entity'] !== null ? t('Save changes') : t('Create')),
        ]);
    }
}
