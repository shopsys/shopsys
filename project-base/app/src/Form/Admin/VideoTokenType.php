<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\ProductVideo\ProductVideoData;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VideoTokenType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('videoToken', TextType::class, [
            'required' => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter video ID',
                ]),
            ],
            'label' => false,
        ]);

        $builder->add('videoTokenDescriptions', LocalizedType::class, [
            'required' => true,
            'entry_options' => [
                'required' => true,
            ],
            'label' => t('Description'),
        ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductVideoData::class,
            ]);
    }
}
