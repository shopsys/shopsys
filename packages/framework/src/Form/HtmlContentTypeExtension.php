<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\HtmlContentDataTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HtmlContentTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly HtmlContentDataTransformer $htmlContentDataTransformer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'contains_html' => false,
        ]);

        $resolver->setAllowedTypes('contains_html', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['contains_html'] === false) {
            return;
        }

        $builder->addModelTransformer($this->htmlContentDataTransformer);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getExtendedTypes(): iterable
    {
        yield TextareaType::class;
    }
}
