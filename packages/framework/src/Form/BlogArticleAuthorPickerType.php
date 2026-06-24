<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\BlogArticleAuthorIdToBlogArticleAuthorTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

final class BlogArticleAuthorPickerType extends AbstractType
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly BlogArticleAuthorIdToBlogArticleAuthorTransformer $blogArticleAuthorIdToBlogArticleAuthorTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->blogArticleAuthorIdToBlogArticleAuthorTransformer);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'picker_url' => $this->router->generate(
                'admin_blogarticleauthorpicker_picksingle',
                ['jsInstanceId' => '__js_instance_id__'],
            ),
            'picker_title' => t('Choose author'),
            'placeholder' => t('Choose author'),
            'item_name' => 'name',
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return SinglePickerType::class;
    }
}
