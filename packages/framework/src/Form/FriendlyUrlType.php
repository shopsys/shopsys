<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class FriendlyUrlType extends AbstractType
{
    protected const string SLUG_REGEX = '/^[\w_\-\/]+$/';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(UrlListData::FIELD_DOMAIN, DomainType::class, [
            'displayUrl' => true,
            'required' => true,
            'limit_domains_by_ids' => $options['limit_domains_by_ids'],
        ]);

        $builder->add(UrlListData::FIELD_SLUG, TextType::class, [
            'required' => true,
            'attr' => [
                'placeholder' => 'slug',
            ],
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Regex(static::SLUG_REGEX),
            ],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'limit_domains_by_ids' => [],
            ])
            ->setAllowedTypes('limit_domains_by_ids', 'array');
    }
}
