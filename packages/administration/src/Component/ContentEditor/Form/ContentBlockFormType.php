<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\Form;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContainerContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One block of the document. The fields of the block and its slots depend on the block type,
 * which is known only from the data, so they are added when the data is set or submitted.
 */
final class ContentBlockFormType extends AbstractType
{
    public function __construct(
        private readonly ContentBlockRegistry $contentBlockRegistry,
        private readonly BlockFormBuilder $blockFormBuilder,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'variables' => [],
                'label' => false,
            ])
            ->setAllowedTypes('variables', 'array');
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('type', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $this->resetBlockFields($event->getForm(), $event->getData()['type'] ?? null, $options['variables']);
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options): void {
            $this->onPreSubmit($event, $options['variables']);
        });
    }

    /**
     * The block may be at another position than when the form was built, so its fields are built again for the submitted type
     *
     * @param array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}> $variables
     */
    private function onPreSubmit(FormEvent $event, array $variables): void
    {
        $submittedBlock = $event->getData();
        $type = $submittedBlock['type'] ?? null;

        if (is_string($type) && $this->contentBlockRegistry->has($type) && is_array($submittedBlock['data'] ?? null)) {
            $submittedBlock['data'] = $this->contentBlockRegistry->get($type)->normalizeData($submittedBlock['data']);
            $event->setData($submittedBlock);
        }

        $this->resetBlockFields($event->getForm(), $type, $variables);
    }

    /**
     * @param array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}> $variables
     */
    private function resetBlockFields(FormInterface $form, mixed $type, array $variables): void
    {
        $form->remove('data');
        $form->remove('children');

        if (!is_string($type) || !$this->contentBlockRegistry->has($type)) {
            return;
        }

        $block = $this->contentBlockRegistry->get($type);
        $form->add($this->blockFormBuilder->createDataForm($block, $variables));

        if ($block instanceof ContainerContentBlockInterface) {
            $form->add('children', CollectionType::class, [
                'entry_type' => CollectionType::class,
                'entry_options' => [
                    'entry_type' => self::class,
                    'entry_options' => ['variables' => $variables],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
            ]);
        }
    }
}
