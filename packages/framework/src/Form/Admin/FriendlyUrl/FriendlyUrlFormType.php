<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGridFactory;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FriendlyUrlFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension $dateTimeFormatterExtension
     */
    public function __construct(private readonly DateTimeFormatterExtension $dateTimeFormatterExtension)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData $friendlyUrlData */
        $friendlyUrlData = $options['data'];

        $builder
            ->add(
                'slug',
                DisplayOnlyType::class,
                [
                    'compound' => false,
                    'data' => $friendlyUrlData->slug,
                    'attr' => [
                        'class' => 'input--auto-size',
                    ],
                ],
            )
            ->add(
                'routeName',
                DisplayOnlyType::class,
                [
                    'compound' => false,
                    'data' => FriendlyUrlGridFactory::getReadableNameForRouteName($friendlyUrlData->name),
                    'attr' => [
                        'class' => 'input--auto-size',
                    ],
                ],
            )
            ->add(
                'entityId',
                DisplayOnlyType::class,
                [
                    'compound' => false,
                    'data' => $friendlyUrlData->entityId,
                    'attr' => [
                        'class' => 'input--auto-size',
                    ],
                ],
            )
            ->add('redirectTo', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(\/)|(https?:\/\/).*$/',
                        'message' => 'Redirect target must be relative path (with leading slash) or absolute path starting by protocol (http:// or https://)',
                    ]),
                ],
                'attr' => ['class' => 'input--auto-size'],
            ])
            ->add(
                'redirectCode',
                ChoiceType::class,
                [
                    'placeholder' => t('Choose type of redirect'),
                    'choices' => [
                        t('301 (Permanent redirect)') => 301,
                        t('302 (Temporary redirect)') => 302,
                    ],
                ],
            )
            ->add(
                'lastModification',
                DisplayOnlyType::class,
                [
                    'compound' => false,
                    'data' => $this->dateTimeFormatterExtension->formatDateTime($friendlyUrlData->lastModification),
                    'attr' => ['class' => 'input--auto-size'],
                ],
            );
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FriendlyUrlData::class,
            'attr' => ['novalidate' => 'novalidate'],
            'constraints' => [
                new Callback([$this, 'checkRedirectValidity']),
            ],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlData $friendlyUrlData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function checkRedirectValidity(FriendlyUrlData $friendlyUrlData, ExecutionContextInterface $context): void
    {
        if ($friendlyUrlData->redirectTo !== null
            && $friendlyUrlData->redirectCode === null
        ) {
            $context->buildViolation(t('You have to set redirect code for redirect.'))
                ->atPath('redirectCode')
                ->addViolation();
        }
    }
}
