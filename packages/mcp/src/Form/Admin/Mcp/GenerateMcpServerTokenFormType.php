<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Form\Admin\Mcp;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GenerateMcpServerTokenFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hasActiveToken = $options['has_active_token'];

        $builder->add('generate', SubmitType::class, [
            'label' => $hasActiveToken ? t('Regenerate MCP token') : t('Generate MCP token'),
            'attr' => $hasActiveToken ? [
                'data-confirm-window' => null,
                'data-confirm-style' => 'warning',
                'data-confirm-message' => t('Generating a new MCP token revokes the previous one immediately. Do you want to continue?'),
            ] : [],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'csrf_token_id' => 'generate_mcp_server_token',
            'has_active_token' => false,
            'method' => Request::METHOD_POST,
        ]);
        $resolver->setAllowedTypes('has_active_token', 'bool');
    }
}
