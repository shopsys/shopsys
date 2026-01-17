<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use Override;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class VideoTokenType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('videoToken', TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank([
                    'message' => 'Please enter video ID',
                ]),
            ],
            'label' => 'Youtube ID',
        ]);

        $builder->add('videoTokenDescriptions', LocalizedType::class, [
            'required' => true,
            'entry_options' => [
                'required' => true,
                'constraints' => [
                    new Constraints\Length(
                        ['max' => 245, 'maxMessage' => 'Description cannot be longer than {{ limit }} characters'],
                    ),
                ],
            ],
            'label' => 'Description',
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductVideoData::class,
            ]);
    }
}
