<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type;

use Override;
use Shopsys\AdministrationBundle\Form\Admin\Type\Transformer\ContentDocumentToJsonTransformer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Block based content editor. The form value is a ContentDocument, which is carried to the server as JSON
 * in a hidden input kept up to date by the ContentEditorComponent rendered next to it.
 *
 * Options:
 * - profile: kind of the content, see ContentProfileEnum
 * - allowed_types: types of blocks the editor offers, null for all blocks of the profile
 * - variables: placeholders offered in the rich text editors (mail), each with "label", "placeholder" and "isRequired"
 */
final class ContentEditorType extends AbstractType
{
    public function __construct(
        private readonly ContentDocumentFactory $contentDocumentFactory,
        private readonly ContentDocumentNormalizer $contentDocumentNormalizer,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'profile' => ContentProfileEnum::WEB,
                'allowed_types' => null,
                'variables' => [],
            ])
            ->setAllowedTypes('profile', 'string')
            ->setAllowedTypes('allowed_types', ['array', 'null'])
            ->setAllowedTypes('variables', 'array');
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ContentDocumentToJsonTransformer(
            $this->contentDocumentFactory,
            $this->contentDocumentNormalizer,
            $options['profile'],
            $options['allowed_types'],
        ));
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['profile'] = $options['profile'];
        $view->vars['allowed_types'] = $options['allowed_types'];
        $view->vars['variables'] = $options['variables'];
    }

    #[Override]
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
