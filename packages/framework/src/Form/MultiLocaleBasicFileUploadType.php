<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class MultiLocaleBasicFileUploadType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     */
    public function __construct(
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly UploadedFileConfig $uploadedFileConfig,
        protected readonly UploadedFileDataFactory $uploadedFileDataFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => false,
            'allow_filenames_input' => true,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['multiple'] = $options['multiple'];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->resetModelTransformers();

        $builder
            ->add(
                $builder->create('names', CollectionType::class, [
                    'required' => false,
                    'entry_type' => LocalizedType::class,
                    'allow_add' => true,
                    'entry_options' => [
                        'attr' => [
                            'icon' => true,
                            'iconTitle' => t('Name in the corresponding locale must be filled-in in order to display the file on the storefront'),
                            'iconPlacement' => 'right',
                        ],
                        'label' => '',
                        'entry_options' => [
                            'constraints' => [
                                new Constraints\Length([
                                    'max' => 255,
                                    'maxMessage' => 'Name cannot be longer than {{ limit }} characters',
                                ]),
                            ],
                        ],
                    ],
                ]),
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return BasicFileUploadType::class;
    }
}
