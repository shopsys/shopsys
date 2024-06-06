<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\UserConsentPolicy;

use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserConsentPolicySettingFormType extends AbstractType
{
    public const string USER_CONSENT_POLICY_ARTICLE_FIELD_NAME = 'userConsentPolicyArticle';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     */
    public function __construct(private readonly ArticleFacade $articleFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $articles = $this->articleFacade->getAllByDomainId($options['domain_id']);

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => t('Settings'),
        ]);

        $builderSettingsGroup
            ->add(self::USER_CONSENT_POLICY_ARTICLE_FIELD_NAME, ChoiceType::class, [
                'required' => false,
                'choices' => $articles,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => t('-- Choose article --'),
                'label' => t('User consent policy article'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t(
                        'Choose the article that provides information about how user consent is obtained, managed, and withdrawn on this domain.',
                    ),
                ],
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
