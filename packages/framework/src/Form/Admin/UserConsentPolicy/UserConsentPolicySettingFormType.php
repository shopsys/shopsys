<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\UserConsentPolicy;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserConsentPolicySettingFormType extends AbstractType
{
    public const string USER_CONSENT_POLICY_ARTICLE_FIELD_NAME = 'userConsentPolicyArticle';

    public function __construct(private readonly ArticleFacade $articleFacade)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $articles = $this->articleFacade->getAllByDomainId($options['domain_id']);

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $builderSettingsGroup
            ->add(self::USER_CONSENT_POLICY_ARTICLE_FIELD_NAME, ChoiceType::class, [
                'required' => false,
                'choices' => $articles,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => '-- Choose article --',
                'label' => 'User consent policy article',
                'help' => t(
                    'Choose the article that provides information about how user consent is obtained, managed, and withdrawn on this domain.',
                ),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
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
