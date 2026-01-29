<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Mail;

use Override;
use Shopsys\FrameworkBundle\Form\Constraints\Email;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender\MailTemplateSenderFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MailTemplateSendFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator $currentAdministrator
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender\MailTemplateSenderFacade $mailTemplateSenderFacade
     */
    public function __construct(
        protected readonly CurrentAdministrator $currentAdministrator,
        protected readonly MailTemplateSenderFacade $mailTemplateSenderFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentAdministrator = $this->currentAdministrator->getCurrentlyLoggedAdministrator();
        $labelForEntityIdentifier = $this->mailTemplateSenderFacade->getFormLabelForEntityIdentifier($options['mailTemplate']);
        $builder
            ->add('mailTo', TextType::class, [
                'label' => 'Send mail to',
                'required' => true,
                'data' => $currentAdministrator->getEmail(),
                'constraints' => [
                    new NotBlank(['message' => 'Please enter email address']),
                    new Email(['message' => 'Please enter valid email']),
                ],
            ]);

        if ($labelForEntityIdentifier !== null) {
            $builder
                ->add('entityIdentifier', IntegerType::class, [
                    'label' => $labelForEntityIdentifier,
                    'required' => true,
                    'constraints' => new NotBlank(['message' => 'Please enter an ID']),
                ]);
        }

        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Send',
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('mailTemplate')
            ->setAllowedTypes('mailTemplate', MailTemplate::class)
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
