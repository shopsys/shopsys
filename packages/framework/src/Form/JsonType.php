<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\JsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class JsonType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\JsonTransformer $jsonTransformer
     */
    public function __construct(private readonly JsonTransformer $jsonTransformer)
    {
    }

    /**
     * @return string
     */
    #[Override]
    public function getParent(): string
    {
        return TextareaType::class;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->jsonTransformer);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'empty_data' => '[]',
            'attr' => [
                'rows' => 10,
                'class' => 'monospace js-json-editor',
                //                'style' => 'display: none',
            ],
            //            'constraints' => [
            //                new Assert\Json(message: t('Invalid JSON format.')),
            //            ],
        ]);
    }
}
