<?php

namespace Shopsys\ShopBundle\Form\Front\Gdpr;

use Shopsys\ShopBundle\Form\HoneyPotType;
use Shopsys\ShopBundle\Form\TimedFormTypeExtension;
use Shopsys\ShopBundle\Model\Gdpr\PersonalDataAccessRequestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Email;

class GdprFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Email(['message' => 'Please enter valid e-mail']),
                ],
            ])
            ->add('email2', HoneyPotType::class)
            ->add('send', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'data_class' => PersonalDataAccessRequestData::class,
            TimedFormTypeExtension::OPTION_ENABLED => true,
        ]);
    }
}
