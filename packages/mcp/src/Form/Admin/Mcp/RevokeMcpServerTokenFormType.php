<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Form\Admin\Mcp;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RevokeMcpServerTokenFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('revoke', SubmitType::class, [
            'label' => 'Revoke MCP token',
            'attr' => [
                'data-confirm-window' => null,
                'data-confirm-style' => 'danger',
                'data-confirm-message' => t('Do you really want to revoke the active MCP token?'),
            ],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate',
            ],
            'csrf_token_id' => 'revoke_mcp_server_token',
            'method' => Request::METHOD_POST,
        ]);
    }
}
