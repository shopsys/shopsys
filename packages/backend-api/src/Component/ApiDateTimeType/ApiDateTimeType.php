<?php

declare(strict_types=1);

namespace Shopsys\BackendApiBundle\Component\ApiDateTimeType;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiDateTimeType extends AbstractType implements DataTransformerInterface
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        if ($data === '') {
            return null;
        }
        if ($data === null) {
            return null;
        }
        return DateTime::createFromFormat(DateTime::ATOM, $data);
    }
}
