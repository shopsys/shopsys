<?php

namespace Shopsys\FrameworkBundle\Form\Admin\AiModel;

use Override;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModel;
use Shopsys\FrameworkBundle\Model\Chat\AiModel\AiModelData;
use Symfony\Component\Form\AbstractType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FormTypesBundle\YesNoType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Shopsys\FormTypesBundle\ActionBarType;

class AiModelFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderAiModelData = $builder->create('agentData', GroupType::class, [
            'label' => t('Information about AI model'),
        ]);

        $builderAiModelData
            ->add('name', TextType::class, [
                'label' => t('Model name'),
                'required' => true,
                'disabled' => true,
            ])
            ->add('description', TextType::class, [
                'label' => t('Model description'),
                'required' => false,
            ])
            ->add('isActive', YesNoType::class, [
                'label' => t('Is model active'),
                'required' => true,
            ])
            ->add('isDeprecated', YesNoType::class, [
                'label' => t('Is model depracated'),
                'required' => true,
            ]) ;

        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_aimodel_list',
            'entity' => $options['aiModel'],
        ]);

        $builder->add($builderAiModelData);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['aiModel'])
            ->setAllowedTypes('aiModel', [AiModel::class, 'null'])
            ->setDefaults([
                'data_class' => AiModelData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}