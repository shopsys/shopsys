<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\VectorStore;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\JsonType;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStore;
use Shopsys\FrameworkBundle\Model\Chat\VectorStore\VectorStoreData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class VectorStoreFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderAgentData = $builder->create('vectorStoreData', GroupType::class, [
            'label' => t('Vector store setup'),
        ]);

        $builderAgentData
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => t('Description'),
                'required' => false,
            ])
            ->add('externalId', TextType::class, [
                'label' => t('External ID'),
                'required' => false,
                'disabled' => $options['vectorStore'] !== null,
            ])
            ->add('dataStructure', JsonType::class, [
                'label' => t('Data structure'),
                'required' => false,
            ])
        ;

        $builder->add($builderAgentData);
        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_vectorstore_list',
            'entity' => $options['vectorStore'],
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['vectorStore'])
            ->setAllowedTypes('vectorStore', [VectorStore::class, 'null'])
            ->setDefaults([
                'data_class' => VectorStoreData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
