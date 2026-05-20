<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Form\Admin\Mcp;

use Override;
use Shopsys\McpBundle\Model\OAuth\McpOAuthAuthorizationRequestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class McpOauthAuthorizationFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientId', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('codeChallenge', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('redirectUri', HiddenType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('state', HiddenType::class, [
                'required' => false,
            ])
            ->add('approve', SubmitType::class, [
                'label' => 'Allow access',
            ])
            ->add('deny', SubmitType::class, [
                'label' => 'Deny access',
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate',
            ],
            'csrf_token_id' => 'mcp_oauth_authorize',
            'data_class' => McpOAuthAuthorizationRequestData::class,
            'method' => Request::METHOD_POST,
        ]);
    }
}
