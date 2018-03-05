<?php

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Component\Constraints\FileExtensionMaxLength;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FileUploadType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload
     */
    private $fileUpload;

    /**
     * @var \Symfony\Component\Validator\Constraint[]
     */
    private $fileConstraints;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(FileUpload $fileUpload)
    {
        $this->fileUpload = $fileUpload;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
            'compound' => true,
            'file_constraints' => [],
            'multiple' => false,
        ]);
    }

    /**
     * @param array $value
     * @return string
     */
    public function reverseTransform($value)
    {
        return $value['uploadedFiles'];
    }

    /**
     * @param string $value
     * @return array
     */
    public function transform($value)
    {
        return [
            'uploadedFiles' => (array)$value,
            'file' => null,
        ];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->fileConstraints = array_merge(
            [
                new FileExtensionMaxLength(['limit' => 5]),
            ],
            $options['file_constraints']
        );

        $builder->addModelTransformer($this);
        $builder
            ->add('uploadedFiles', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'allow_add' => true,
                'constraints' => [
                    new Constraints\Callback([$this, 'validateUploadedFiles']),
                ],
            ])
            ->add('file', FileType::class, [
                'multiple' => $options['multiple'],
            ]);

        // fallback for IE9 and older
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param string[]|null $uploadedFiles
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUploadedFiles($uploadedFiles, ExecutionContextInterface $context)
    {
        foreach ($uploadedFiles as $uploadedFile) {
            $filepath = $this->fileUpload->getTemporaryFilepath($uploadedFile);
            $file = new File($filepath, false);

            $validator = $context->getValidator();
            $violations = $validator->validate($file, $this->fileConstraints);
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
        if (is_array($data) && array_key_exists('file', $data) && is_array($data['file'])) {
            $fallbackFiles = $data['file'];
            foreach ($fallbackFiles as $file) {
                if ($file instanceof UploadedFile) {
                    try {
                        $data['uploadedFiles'][] = $this->fileUpload->upload($file);
                    } catch (\Shopsys\FrameworkBundle\Component\FileUpload\Exception\FileUploadException $ex) {
                        $event->getForm()->addError(new FormError(t('File upload failed')));
                    }
                }
            }

            $event->setData($data);
        }
    }
}
