<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Transport;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class TransportFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    public function __construct(VatFacade $vatFacade)
    {
        $this->vatFacade = $vatFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', LocalizedType::class, [
                'main_constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                ],
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                    ],
                ],
            ])
            ->add('domains', DomainsType::class, [
                'required' => false,
            ])
            ->add('hidden', YesNoType::class, ['required' => false])
            ->add('vat', ChoiceType::class, [
                'required' => true,
                'choices' => $this->vatFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter VAT rate']),
                ],
            ])
            ->add('description', LocalizedType::class, [
                'required' => false,
                'entry_type' => TextareaType::class,
            ])
            ->add('instructions', LocalizedType::class, [
                'required' => false,
                'entry_type' => CKEditorType::class,
            ])
            ->add('image', FileUploadType::class, [
                'required' => false,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransportData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
