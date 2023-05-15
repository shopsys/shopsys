<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\FileUpload\Exception\FileUploadException;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Form\Constraints\FileExtensionMaxLength;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AbstractFileUploadType extends AbstractType implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(private readonly FileUpload $fileUpload)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('info_text')
            ->setAllowedTypes('info_text', ['string', 'null'])
            ->setDefaults([
                'error_bubbling' => false,
                'compound' => true,
                'file_constraints' => [],
                'info_text' => null,
            ]);
    }

    /**
     * @param array $value
     * @return string
     */
    public function reverseTransform($value): string
    {
        return $value['uploadedFiles'];
    }

    /**
     * @param string $value
     * @return array
     */
    public function transform($value): array
    {
        return ['uploadedFiles' => (array)$value];
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['info_text'] = $options['info_text'];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fileConstraints = array_merge(
            [
                new FileExtensionMaxLength(['limit' => 5]),
            ],
            $options['file_constraints'],
        );

        $builder->addModelTransformer($this);
        $builder
            ->add('uploadedFiles', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add' => true,
                'constraints' => [
                    new Constraints\Callback(
                        ['callback' => [$this, 'validateUploadedFiles'], 'payload' => $fileConstraints],
                    ),
                ],
            ])
            ->add('uploadedFilenames', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter the filename']),
                        new Constraints\Length(
                            ['max' => 245, 'maxMessage' => 'File name cannot be longer than {{ limit }} characters'],
                        ),
                    ],
                ],
            ]);

        // fallback for IE9 and older
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param string[]|null $uploadedFiles
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     * @param \Symfony\Component\Validator\Constraint[] $fileConstraints
     */
    public function validateUploadedFiles(?array $uploadedFiles, ExecutionContextInterface $context, array $fileConstraints): void
    {
        foreach ($uploadedFiles as $uploadedFile) {
            $filepath = $this->fileUpload->getTemporaryFilepath($uploadedFile);
            $file = new File($filepath, false);

            $validator = $context->getValidator();
            $violations = $validator->validate($file, $fileConstraints);

            foreach ($violations as $violation) {
                $context->addViolation($violation->getMessageTemplate(), $violation->getParameters());
            }
        }
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (!is_array($data) || !array_key_exists('file', $data) || !is_array($data['file'])) {
            return;
        }

        $fallbackFiles = $data['file'];

        foreach ($fallbackFiles as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $data['uploadedFiles'][] = $this->fileUpload->upload($file);
                } catch (FileUploadException $ex) {
                    $event->getForm()->addError(new FormError(t('File upload failed')));
                }
            }
        }

        $event->setData($data);
    }
}
